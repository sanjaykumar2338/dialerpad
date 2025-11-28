<?php

namespace App\Http\Controllers;

use App\Models\EsimCode;
use App\Models\EsimRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EsimController extends Controller
{
    public function showForm(string $uuid): View
    {
        $code = EsimCode::with('type')
            ->where('uuid', $uuid)
            ->where('status', 'active')
            ->firstOrFail();

        return view('public.esim-form', [
            'code' => $code,
            'type' => $code->type,
        ]);
    }

    public function submit(Request $request, string $uuid): RedirectResponse
    {
        $code = EsimCode::with('type')
            ->where('uuid', $uuid)
            ->where('status', 'active')
            ->firstOrFail();

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'device_model' => ['nullable', 'string', 'max:255'],
        ]);

        EsimRequest::create([
            'esim_code_id' => $code->id,
            'esim_type_id' => $code->esim_type_id,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'device_model' => $data['device_model'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your eSIM activation request has been received.');
    }
}
