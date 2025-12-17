<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallCard;
use App\Models\CallSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PbxController extends Controller
{
    /**
     * Validate a call card token for PBX.
     */
    public function validate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
        ]);

        $card = CallCard::where('uuid', $data['token'])->first();

        if (!$card) {
            return response()->json([
                'valid' => false,
                'reason' => 'not_found',
            ]);
        }

        if ($card->status !== 'active' || $card->remaining_minutes <= 0) {
            return response()->json([
                'valid' => false,
                'reason' => 'expired',
                'minutes_left' => 0,
            ]);
        }

        return response()->json([
            'valid' => true,
            'card_uuid' => $card->uuid,
            'minutes_left' => $card->remaining_minutes,
            'prefix' => $card->prefix,
            'status' => 'active',
        ]);
    }

    /**
     * Mark the end of a call and deduct billed minutes.
     */
    public function callEnd(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
            'call_id' => ['required', 'string', 'max:191'],
            'duration_seconds' => ['required', 'integer', 'min:0'],
            'dialed_number' => ['nullable', 'string', 'max:191'],
        ]);

        $card = CallCard::where('uuid', $data['token'])->first();

        if (!$card) {
            return response()->json([
                'ok' => false,
                'reason' => 'not_found',
            ]);
        }

        if ($card->status !== 'active' || $card->remaining_minutes <= 0) {
            return response()->json([
                'ok' => false,
                'reason' => 'expired',
                'minutes_left' => 0,
                'card_status' => 'expired',
            ]);
        }

        $billedMinutes = $data['duration_seconds'] > 0
            ? (int) ceil($data['duration_seconds'] / 60)
            : 0;

        return DB::transaction(function () use ($card, $data, $billedMinutes) {
            $lockedCard = CallCard::where('id', $card->id)->lockForUpdate()->firstOrFail();

            $session = CallSession::where('session_uuid', $data['call_id'])
                ->lockForUpdate()
                ->first();

            if ($session && $session->status === 'ended') {
                $previousBilled = $session->duration_seconds > 0
                    ? (int) ceil($session->duration_seconds / 60)
                    : 0;

                return response()->json([
                    'ok' => true,
                    'billed_minutes' => $previousBilled,
                    'minutes_left' => $session->remaining_minutes_after_call ?? $lockedCard->remaining_minutes,
                    'card_status' => $lockedCard->status,
                ]);
            }

            $lockedCard->used_minutes = min(
                $lockedCard->total_minutes,
                $lockedCard->used_minutes + $billedMinutes
            );
            $lockedCard->save();

            $minutesLeft = $lockedCard->remaining_minutes;

            if (!$session) {
                $session = new CallSession([
                    'session_uuid' => $data['call_id'],
                ]);
            }

            $dialedNumber = $data['dialed_number'] ?? $session->dialed_number ?? 'unknown';

            $session->fill([
                'call_card_id' => $lockedCard->id,
                'dialed_number' => $dialedNumber,
                'full_number' => $session->full_number ?? $dialedNumber,
                'duration_seconds' => $data['duration_seconds'],
                'remaining_minutes_after_call' => $minutesLeft,
                'status' => 'ended',
            ]);

            $session->ended_at = now();
            $session->save();

            return response()->json([
                'ok' => true,
                'billed_minutes' => $billedMinutes,
                'minutes_left' => $minutesLeft,
                'card_status' => $lockedCard->status,
            ]);
        });
    }
}
