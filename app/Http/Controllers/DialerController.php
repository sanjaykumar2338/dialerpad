<?php

namespace App\Http\Controllers;

use App\Models\CallCard;
use App\Models\CallSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DialerController extends Controller
{
    public function show(string $uuid)
    {
        $card = CallCard::where('uuid', $uuid)->firstOrFail();

        if ($card->status === 'expired' || $card->remaining_minutes <= 0) {
            return view('public.card-expired', compact('card'));
        }

        return view('public.dialer', compact('card'));
    }

    public function startCall(Request $request, string $uuid)
    {
        $request->validate([
            'dialed_number' => ['required','string','max:30'],
        ]);

        $card = CallCard::where('uuid', $uuid)->firstOrFail();

        if ($card->status !== 'active' || $card->remaining_minutes <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Card is not active or has no minutes remaining.',
            ], 422);
        }

        $normalizedNumber = $this->normalizeToE164($request->dialed_number, $card->prefix);
        if ($normalizedNumber === '') {
            return response()->json([
                'success' => false,
                'message' => 'Enter a valid number to dial.',
            ], 422);
        }

        $session = CallSession::create([
            'call_card_id'   => $card->id,
            'session_uuid'   => Str::uuid(),
            'dialed_number'  => $normalizedNumber,
            'full_number'    => $normalizedNumber,
            'started_at'     => now(),
            'status'         => 'started',
        ]);

        return response()->json([
            'success'       => true,
            'session_uuid'  => $session->session_uuid,
            'remaining_min' => $card->remaining_minutes,
        ]);
    }

    public function endCall(Request $request, string $uuid)
    {
        $request->validate([
            'session_uuid'     => ['required','uuid'],
            'duration_seconds' => ['required','integer','min:0'],
        ]);

        $card = CallCard::where('uuid', $uuid)->firstOrFail();

        return DB::transaction(function () use ($request, $card) {
            $card = CallCard::where('id', $card->id)->lockForUpdate()->firstOrFail();
            $session = CallSession::where('session_uuid', $request->session_uuid)
                ->where('call_card_id', $card->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status === 'completed') {
                return response()->json([
                    'success'       => true,
                    'remaining_min' => $card->remaining_minutes,
                    'card_status'   => $card->status,
                ]);
            }

            $session->ended_at         = now();
            $session->duration_seconds = $request->duration_seconds;
            $session->status           = 'completed';

            $usedMinutes = (int) ceil($session->duration_seconds / 60);
            $card->used_minutes = min($card->total_minutes, $card->used_minutes + $usedMinutes);
            $card->markExpiredIfNeeded();
            $card->refresh();

            $session->remaining_minutes_after_call = $card->remaining_minutes;
            $session->save();

            return response()->json([
                'success'       => true,
                'remaining_min' => $card->remaining_minutes,
                'card_status'   => $card->status,
            ]);
        });
    }

    private function normalizeToE164(?string $number, ?string $enforcedPrefix = null): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if ($digits === '') {
            return '';
        }

        $defaultPrefix = $this->dialPrefixDefault();
        $gatewayPrefix = $this->dialPrefixGateway();
        $gatewayCombo = $gatewayPrefix !== '' ? $gatewayPrefix . $defaultPrefix : '';

        if ($gatewayCombo !== '' && str_starts_with($digits, $gatewayCombo)) {
            return $digits;
        }

        $enforcedPrefix = $this->resolveDialPrefix($enforcedPrefix);
        if ($enforcedPrefix !== '' && str_starts_with($digits, $enforcedPrefix)) {
            return $digits;
        }

        if ($gatewayCombo !== '' && $enforcedPrefix === $gatewayCombo && str_starts_with($digits, $defaultPrefix)) {
            return $gatewayPrefix . $digits;
        }

        return $enforcedPrefix . ltrim($digits, '0');
    }

    private function resolveDialPrefix(?string $prefix): string
    {
        $digits = preg_replace('/\D+/', '', (string) $prefix);
        if ($digits !== '') {
            return $digits;
        }

        return $this->dialPrefixDefault();
    }

    private function dialPrefixDefault(): string
    {
        return preg_replace('/\D+/', '', (string) config('pbx.dial_prefix_default', '223'));
    }

    private function dialPrefixGateway(): string
    {
        return preg_replace('/\D+/', '', (string) config('pbx.dial_prefix_gateway', ''));
    }
}
