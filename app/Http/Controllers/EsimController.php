<?php

namespace App\Http\Controllers;

use App\Mail\EsimQrMail;
use App\Models\EsimCode;
use App\Models\EsimRequest;
use App\Services\MobimatterClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EsimController extends Controller
{
    public function showForm(string $uuid): View
    {
        $code = EsimCode::with('type')
            ->where('uuid', $uuid)
            ->where('status', 'unused')
            ->first();

        if (!$code) {
            return response()->view('public.esim-used', [], 410);
        }

        return view('public.esim-form', [
            'code' => $code,
            'type' => $code->type,
        ]);
    }

    public function submit(Request $request, string $uuid, MobimatterClient $mobimatter): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'device_model' => ['nullable', 'string', 'max:255'],
        ]);

        $code = null;
        $esimRequest = null;
        $type = null;

        DB::transaction(function () use (&$code, &$esimRequest, &$type, $uuid, $data) {
            $code = EsimCode::with('type')
                ->where('uuid', $uuid)
                ->lockForUpdate()
                ->first();

            if (!$code || $code->status !== 'unused') {
                abort(410, 'This QR has already been used.');
            }

            $type = $code->type;

            $esimRequest = EsimRequest::create([
                'esim_code_id' => $code->id,
                'esim_type_id' => $code->esim_type_id,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'device_model' => $data['device_model'] ?? null,
                'status' => 'pending',
            ]);

            $code->status = 'disabled'; // reserve while we call provider
            $code->save();
        });

        if (empty($code->product_id ?? $type?->product_id)) {
            DB::transaction(function () use ($code, $esimRequest): void {
                if ($esimRequest) {
                    $esimRequest->update([
                        'status' => 'failed',
                        'provider_response' => ['error' => 'Missing product ID for this plan.'],
                    ]);
                }
                if ($code) {
                    $code->status = 'unused';
                    $code->save();
                }
            });

            return back()->withErrors('Activation failed: missing product ID for this plan.');
        }

        try {
            $response = $mobimatter->purchase(
                $code->product_id ?? $type->product_id,
                [
                    'customer' => [
                        'name' => $data['full_name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'device_model' => $data['device_model'] ?? null,
                    ],
                    'metadata' => [
                        'esim_code_uuid' => $code->uuid,
                        'esim_type_id' => $code->esim_type_id,
                    ],
                ]
            );
        } catch (\Throwable $e) {
            DB::transaction(function () use ($code, $esimRequest, $e): void {
                if ($esimRequest) {
                    $esimRequest->update([
                        'status' => 'failed',
                        'provider_response' => ['error' => $e->getMessage()],
                    ]);
                }
                if ($code) {
                    $code->status = 'unused';
                    $code->save();
                }
            });

            return back()->withErrors('Activation failed: ' . $e->getMessage());
        }

        DB::transaction(function () use ($code, $esimRequest, $response): void {
            if ($esimRequest) {
                $esimRequest->update([
                    'status' => 'processed',
                    'provider_response' => $response,
                ]);
            }

            if ($code) {
                $code->status = 'used';
                $code->used_at = now();
                $code->save();
            }
        });

        $qrContent = $response['qr'] ?? $response['qrCode'] ?? $response['qr_svg'] ?? $response['qrImage'] ?? null;

        if (!empty($data['email'])) {
            Mail::to($data['email'])->send(new EsimQrMail(
                $data['full_name'],
                $qrContent,
                $response
            ));
        }

        return back()->with('success', 'Your eSIM has been issued. Check your email for the QR.');
    }
}
