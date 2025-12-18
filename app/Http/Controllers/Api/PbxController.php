<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallCard;
use App\Models\CallSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PbxController extends Controller
{
    /**
     * Validate a call card token for PBX.
     */
    public function validate(Request $request): JsonResponse|Response
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            $token = (string) $request->input('token');

            Log::info('PBX validate', [
                'token' => $token,
                'found' => false,
                'minutes_left' => null,
                'status' => null,
                'reason' => 'invalid_token',
                'errors' => $validator->errors()->all(),
            ]);

            return $this->respondPbx($request, [
                'valid' => false,
                'token' => $token,
                'reason' => 'invalid_token',
            ], false);
        }

        $token = $validator->validated()['token'];
        $card = CallCard::where('uuid', $token)->first();

        if (!$card) {
            Log::info('PBX validate', [
                'token' => $token,
                'found' => false,
                'minutes_left' => null,
                'status' => null,
            ]);

            return $this->respondPbx($request, [
                'valid' => false,
                'token' => $token,
                'reason' => 'not_found',
            ], false);
        }

        $minutesLeft = max(0, $card->remaining_minutes);
        $status = $card->status ?? 'unknown';
        $isExpired = $status !== 'active' || $minutesLeft <= 0;
        $responseStatus = $isExpired ? 'expired' : 'active';
        $minutesLeftForReturn = $isExpired ? 0 : $minutesLeft;

        Log::info('PBX validate', [
            'token' => $token,
            'found' => true,
            'minutes_left' => $minutesLeftForReturn,
            'status' => $responseStatus,
        ]);

        if ($isExpired) {
            return $this->respondPbx($request, [
                'valid' => false,
                'token' => $token,
                'reason' => 'expired',
                'status' => $responseStatus,
                'minutes_left' => $minutesLeftForReturn,
            ], false);
        }

        return $this->respondPbx($request, [
            'valid' => true,
            'token' => $token,
            'card_uuid' => $card->uuid,
            'minutes_left' => $minutesLeftForReturn,
            'prefix' => $card->prefix,
            'status' => $responseStatus,
        ], true);
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

            $normalizedNumber = $data['dialed_number']
                ? $this->normalizeToE164($data['dialed_number'], $lockedCard->prefix)
                : null;

            $dialedNumber = $normalizedNumber ?? $session?->dialed_number ?? 'unknown';

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

    private function wantsJson(Request $request): bool
    {
        $accept = (string) $request->header('Accept');
        return str_contains($accept, 'application/json');
    }

    private function respondPbx(Request $request, array $jsonPayload, bool $valid): JsonResponse|Response
    {
        if ($this->wantsJson($request)) {
            return response()->json($jsonPayload, 200);
        }

        return response($valid ? 'valid' : 'invalid', 200)
            ->header('Content-Type', 'text/plain');
    }
}
