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

        $dialed = preg_replace('/\D/', '', $request->dialed_number);
        if ($dialed === '') {
            return response()->json([
                'success' => false,
                'message' => 'Enter a valid number to dial.',
            ], 422);
        }
        $full = $card->prefix . $dialed;

        $session = CallSession::create([
            'call_card_id'   => $card->id,
            'session_uuid'   => Str::uuid(),
            'dialed_number'  => $dialed,
            'full_number'    => $full,
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
}
