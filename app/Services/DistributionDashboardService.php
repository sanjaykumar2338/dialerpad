<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\BatchRequest;
use App\Models\CallCard;
use App\Models\EsimCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DistributionDashboardService
{
    public function payload(User $user): array
    {
        $includeAllAccounts = (bool) $user->is_admin;

        return [
            'metrics' => $this->metrics($user, $includeAllAccounts),
            'batches' => $this->latestBatches($user, $includeAllAccounts),
            'activity' => $this->latestActivity($user, $includeAllAccounts),
            'requests' => $this->latestRequests($user, $includeAllAccounts),
        ];
    }

    public function reports(User $user): array
    {
        $includeAllAccounts = (bool) $user->is_admin;

        return [
            'metrics' => $this->metrics($user, $includeAllAccounts),
            'requestCounts' => $this->requestStatusCounts($user, $includeAllAccounts),
            'productCounts' => $this->requestProductCounts($user, $includeAllAccounts),
            'batchPerformance' => $this->latestBatches($user, $includeAllAccounts, 8),
            'inventoryStatus' => $this->inventoryStatus($user, $includeAllAccounts),
        ];
    }

    public function metrics(User $user, bool $includeAllAccounts = false): array
    {
        $callCards = $this->scopeInventory(CallCard::query(), $user, $includeAllAccounts);
        $esimCodes = $this->scopeInventory(EsimCode::query(), $user, $includeAllAccounts);

        $callTotal = (clone $callCards)->count();
        $esimTotal = (clone $esimCodes)->count();

        $callActivated = (clone $callCards)
            ->where(function (Builder $query) {
                $query->where('used_minutes', '>', 0)
                    ->orWhere('status', 'expired');
            })
            ->count();
        $esimActivated = (clone $esimCodes)->whereIn('status', ['used', 'activated'])->count();

        $callRemaining = (clone $callCards)
            ->where('status', 'active')
            ->where('used_minutes', 0)
            ->count();
        $esimRemaining = (clone $esimCodes)->where('status', 'unused')->count();

        $expired = (clone $callCards)->where('status', 'expired')->count()
            + (clone $esimCodes)->where('status', 'expired')->count();

        $total = $callTotal + $esimTotal;
        $activated = $callActivated + $esimActivated;

        return [
            'total' => $total,
            'activated' => $activated,
            'remaining' => $callRemaining + $esimRemaining,
            'activation_rate' => $total > 0 ? round(($activated / $total) * 100, 1) : 0,
            'expired' => $expired,
        ];
    }

    public function latestBatches(User $user, bool $includeAllAccounts = false, int $limit = 5): array
    {
        return $this->scopeAccount(Batch::with('account')->latest(), $user, $includeAllAccounts)
            ->limit($limit)
            ->get()
            ->map(fn (Batch $batch) => $this->formatBatch($batch))
            ->all();
    }

    public function formatBatch(Batch $batch): array
    {
        $stats = $this->batchStats($batch);

        return [
            'id' => $batch->id,
            'batch_id' => $batch->batch_id,
            'account_id' => $batch->account_id,
            'account_name' => $batch->account?->name,
            'product_type' => $batch->product_type,
            'product_label' => $batch->productLabel(),
            'status' => $batch->status,
            'total_cards' => $stats['total'],
            'used_cards' => $stats['used'],
            'remaining_cards' => $stats['remaining'],
            'expired_cards' => $stats['expired'],
            'activation_rate' => $stats['activation_rate'],
            'created_at' => $batch->created_at?->toIso8601String(),
            'sent_at' => $batch->sent_at?->toIso8601String(),
            'completed_at' => $batch->completed_at?->toIso8601String(),
            'delivery_document_path' => $batch->delivery_document_path,
            'delivery_document_url' => $batch->delivery_document_path ? asset('storage/'.$batch->delivery_document_path) : null,
        ];
    }

    public function batchStats(Batch $batch): array
    {
        if ($batch->product_type === BatchRequest::PRODUCT_ESIM) {
            $query = EsimCode::where('batch_id', $batch->batch_id);
            $used = (clone $query)->whereIn('status', ['used', 'activated'])->count();
            $remaining = (clone $query)->where('status', 'unused')->count();
            $expired = (clone $query)->where('status', 'expired')->count();
        } else {
            $query = CallCard::where('batch_id', $batch->batch_id);
            $used = (clone $query)
                ->where(function (Builder $query) {
                    $query->where('used_minutes', '>', 0)
                        ->orWhere('status', 'expired');
                })
                ->count();
            $remaining = (clone $query)
                ->where('status', 'active')
                ->where('used_minutes', 0)
                ->count();
            $expired = (clone $query)->where('status', 'expired')->count();
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $total = (int) $batch->total_cards;
        }

        return [
            'total' => $total,
            'used' => $used,
            'remaining' => $remaining,
            'expired' => $expired,
            'activation_rate' => $total > 0 ? round(($used / $total) * 100, 1) : 0,
        ];
    }

    public function latestActivity(User $user, bool $includeAllAccounts = false, int $limit = 10): array
    {
        $loggedActivity = $this->scopeAccount(ActivityLog::with('account')->latest(), $user, $includeAllAccounts)
            ->limit($limit)
            ->get();

        if ($loggedActivity->isNotEmpty()) {
            return $loggedActivity
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'type' => $log->event,
                    'title' => $this->activityTitle($log->event),
                    'description' => $log->description,
                    'account_name' => $log->account?->name,
                    'occurred_at' => $log->created_at?->toIso8601String(),
                ])
                ->all();
        }

        return $this->fallbackActivity($user, $includeAllAccounts, $limit);
    }

    public function latestRequests(User $user, bool $includeAllAccounts = false, int $limit = 5): array
    {
        return $this->scopeAccount(BatchRequest::with('account')->latest(), $user, $includeAllAccounts)
            ->limit($limit)
            ->get()
            ->map(fn (BatchRequest $request) => [
                'id' => $request->id,
                'account_id' => $request->account_id,
                'account_name' => $request->account?->name,
                'product_type' => $request->product_type,
                'product_label' => $request->productLabel(),
                'quantity' => $request->quantity,
                'status' => $request->status,
                'notes' => $request->notes,
                'created_at' => $request->created_at?->toIso8601String(),
                'updated_at' => $request->updated_at?->toIso8601String(),
            ])
            ->all();
    }

    public function requestStatusCounts(User $user, bool $includeAllAccounts = false): Collection
    {
        $counts = $this->scopeAccount(BatchRequest::query(), $user, $includeAllAccounts)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(BatchRequest::STATUSES)->mapWithKeys(
            fn (string $status) => [$status => (int) ($counts[$status] ?? 0)]
        );
    }

    public function requestProductCounts(User $user, bool $includeAllAccounts = false): Collection
    {
        $counts = $this->scopeAccount(BatchRequest::query(), $user, $includeAllAccounts)
            ->selectRaw('product_type, COUNT(*) as total')
            ->groupBy('product_type')
            ->pluck('total', 'product_type');

        return collect(BatchRequest::PRODUCTS)->mapWithKeys(
            fn (string $product) => [$product => (int) ($counts[$product] ?? 0)]
        );
    }

    public function inventoryStatus(User $user, bool $includeAllAccounts = false): array
    {
        $callCards = $this->scopeInventory(CallCard::query(), $user, $includeAllAccounts);
        $esimCodes = $this->scopeInventory(EsimCode::query(), $user, $includeAllAccounts);

        $unused = (clone $callCards)
            ->where('status', 'active')
            ->where('used_minutes', 0)
            ->count()
            + (clone $esimCodes)->where('status', 'unused')->count();

        $activated = (clone $callCards)
            ->where('status', 'active')
            ->where('used_minutes', '>', 0)
            ->count()
            + (clone $esimCodes)->whereIn('status', ['used', 'activated'])->count();

        $expired = (clone $callCards)->where('status', 'expired')->count()
            + (clone $esimCodes)->where('status', 'expired')->count();

        return [
            'unused' => $unused,
            'activated' => $activated,
            'expired' => $expired,
        ];
    }

    public function scopeAccount(Builder $query, User $user, bool $includeAllAccounts = false): Builder
    {
        if (! $includeAllAccounts) {
            $query->where('account_id', $user->id);
        }

        return $query;
    }

    private function scopeInventory(Builder $query, User $user, bool $includeAllAccounts): Builder
    {
        if (! $includeAllAccounts) {
            $query->where('account_id', $user->id);
        }

        return $query;
    }

    private function fallbackActivity(User $user, bool $includeAllAccounts, int $limit): array
    {
        $items = collect();

        $this->scopeAccount(Batch::with('account')->latest(), $user, $includeAllAccounts)
            ->limit($limit)
            ->get()
            ->each(function (Batch $batch) use ($items): void {
                $items->push([
                    'id' => 'batch-'.$batch->id,
                    'type' => 'batch_received',
                    'title' => 'Batch received',
                    'description' => $batch->productLabel().' batch '.$batch->batch_id.' was generated.',
                    'account_name' => $batch->account?->name,
                    'occurred_at' => $batch->created_at?->toIso8601String(),
                    'sort_at' => $batch->created_at,
                ]);
            });

        $this->scopeAccount(BatchRequest::with('account')->latest(), $user, $includeAllAccounts)
            ->limit($limit)
            ->get()
            ->each(function (BatchRequest $request) use ($items): void {
                $items->push([
                    'id' => 'request-'.$request->id,
                    'type' => 'request_'.$request->status,
                    'title' => 'Request '.$request->status,
                    'description' => $request->productLabel().' request for '.number_format($request->quantity).' cards is '.$request->status.'.',
                    'account_name' => $request->account?->name,
                    'occurred_at' => $request->updated_at?->toIso8601String(),
                    'sort_at' => $request->updated_at,
                ]);
            });

        $this->cardActivationActivity($user, $includeAllAccounts, $limit)
            ->each(fn (array $activity) => $items->push($activity));

        return $items
            ->sortByDesc('sort_at')
            ->take($limit)
            ->values()
            ->map(function (array $item) {
                unset($item['sort_at']);

                return $item;
            })
            ->all();
    }

    private function cardActivationActivity(User $user, bool $includeAllAccounts, int $limit): Collection
    {
        $items = collect();

        $this->scopeInventory(EsimCode::with('account')->whereIn('status', ['used', 'activated'])->whereNotNull('used_at'), $user, $includeAllAccounts)
            ->latest('used_at')
            ->limit($limit)
            ->get()
            ->each(function (EsimCode $code) use ($items): void {
                $items->push([
                    'id' => 'esim-'.$code->id,
                    'type' => 'cards_activated',
                    'title' => 'Card activated',
                    'description' => 'eSIM QR '.$code->uuid.' was activated.',
                    'account_name' => $code->account?->name,
                    'occurred_at' => $code->used_at?->toIso8601String(),
                    'sort_at' => $code->used_at,
                ]);
            });

        $this->scopeInventory(CallCard::with('account')->where('used_minutes', '>', 0), $user, $includeAllAccounts)
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->each(function (CallCard $card) use ($items): void {
                $occurredAt = $card->activated_at ?? $card->updated_at;

                $items->push([
                    'id' => 'call-card-'.$card->id,
                    'type' => 'cards_activated',
                    'title' => 'Card activated',
                    'description' => 'Call card '.$card->name.' was activated.',
                    'account_name' => $card->account?->name,
                    'occurred_at' => $occurredAt?->toIso8601String(),
                    'sort_at' => $occurredAt,
                ]);
            });

        return $items;
    }

    private function activityTitle(string $event): string
    {
        return match ($event) {
            'batch_received', 'batch_generated' => 'Batch received',
            'cards_activated' => 'Cards activated',
            'request_approved' => 'Request approved',
            'request_generated' => 'Request generated',
            'request_sent' => 'Request sent',
            'request_completed' => 'Request completed',
            default => ucwords(str_replace('_', ' ', $event)),
        };
    }
}
