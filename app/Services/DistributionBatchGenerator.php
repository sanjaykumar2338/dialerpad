<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\BatchRequest;
use App\Models\CallCard;
use App\Models\EsimCode;
use App\Models\EsimType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DistributionBatchGenerator
{
    public function __construct(
        private readonly QrCodeService $qrCodeService
    ) {}

    public function generate(BatchRequest $request, User $admin, array $settings): Batch
    {
        return DB::transaction(function () use ($request, $admin, $settings): Batch {
            $request = BatchRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($request->status !== BatchRequest::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'request' => 'Only approved requests can be generated.',
                ]);
            }

            if ($request->batch()->exists()) {
                throw ValidationException::withMessages([
                    'request' => 'This request already has a generated batch.',
                ]);
            }

            $batch = Batch::create([
                'batch_id' => (string) Str::uuid(),
                'account_id' => $request->account_id,
                'batch_request_id' => $request->id,
                'product_type' => $request->product_type,
                'status' => Batch::STATUS_GENERATED,
                'total_cards' => $request->quantity,
                'generated_by' => $admin->id,
            ]);

            if ($request->product_type === BatchRequest::PRODUCT_ESIM) {
                $this->generateEsimCodes($request, $batch, $settings);
            } else {
                $this->generateCallCards($request, $batch, $settings, $admin);
            }

            $request->update([
                'status' => BatchRequest::STATUS_GENERATED,
                'generation_settings' => $settings,
                'generated_at' => now(),
            ]);

            ActivityLog::create([
                'account_id' => $request->account_id,
                'actor_id' => $admin->id,
                'batch_id' => $batch->batch_id,
                'batch_request_id' => $request->id,
                'event' => 'batch_generated',
                'description' => $request->productLabel().' batch '.$batch->batch_id.' generated with '.number_format($request->quantity).' cards.',
                'metadata' => [
                    'product_type' => $request->product_type,
                    'quantity' => $request->quantity,
                ],
            ]);

            return $batch;
        });
    }

    private function generateEsimCodes(BatchRequest $request, Batch $batch, array $settings): void
    {
        $type = EsimType::findOrFail($settings['esim_type_id']);

        if (empty($type->product_id)) {
            throw ValidationException::withMessages([
                'esim_type_id' => 'Selected eSIM plan is missing a Mobimatter product ID.',
            ]);
        }

        $label = $settings['label'] ?? ('Request #'.$request->id);

        for ($i = 1; $i <= $request->quantity; $i++) {
            $code = EsimCode::create([
                'uuid' => (string) Str::uuid(),
                'esim_type_id' => $type->id,
                'product_id' => $type->product_id,
                'account_id' => $request->account_id,
                'batch_id' => $batch->batch_id,
                'label' => $label,
                'status' => 'unused',
            ]);

            $this->qrCodeService->generateForEsimCode($code, url('/esim/'.$code->uuid));
        }
    }

    private function generateCallCards(BatchRequest $request, Batch $batch, array $settings, User $admin): void
    {
        $baseName = ($settings['name_prefix'] ?? null) ?: ('Call Card Request '.$request->id);

        for ($i = 1; $i <= $request->quantity; $i++) {
            $card = CallCard::create([
                'name' => $baseName.' #'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'prefix' => $settings['prefix'],
                'total_minutes' => (int) $settings['total_minutes'],
                'notes' => $request->notes,
                'uuid' => (string) Str::uuid(),
                'created_by' => $admin->id,
                'account_id' => $request->account_id,
                'batch_id' => $batch->batch_id,
            ]);

            $this->qrCodeService->generateForCallCard($card);
        }
    }
}
