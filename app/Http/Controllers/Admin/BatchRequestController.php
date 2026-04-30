<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\BatchRequest;
use App\Models\EsimType;
use App\Services\DistributionBatchGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatchRequestController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->query('status'),
            'product_type' => $request->query('product_type'),
            'search' => $request->query('search'),
        ];

        $query = BatchRequest::with(['account', 'batch'])->latest();

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['product_type']) {
            $query->where('product_type', $filters['product_type']);
        }

        if ($filters['search']) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', $term)
                    ->orWhereHas('account', function ($accountQuery) use ($term) {
                        $accountQuery->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        return view('admin.batch-requests.index', [
            'requests' => $query->paginate(12)->withQueryString(),
            'filters' => $filters,
            'statuses' => BatchRequest::STATUSES,
            'products' => BatchRequest::PRODUCTS,
            'esimTypes' => EsimType::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function approve(Request $request, BatchRequest $batchRequest): RedirectResponse
    {
        if ($batchRequest->status !== BatchRequest::STATUS_PENDING) {
            return back()->withErrors('Only pending requests can be approved.');
        }

        $batchRequest->update([
            'status' => BatchRequest::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ActivityLog::create([
            'account_id' => $batchRequest->account_id,
            'actor_id' => $request->user()->id,
            'batch_request_id' => $batchRequest->id,
            'event' => 'request_approved',
            'description' => $batchRequest->productLabel().' request #'.$batchRequest->id.' was approved.',
            'metadata' => [
                'product_type' => $batchRequest->product_type,
                'quantity' => $batchRequest->quantity,
            ],
        ]);

        return back()->with('status', 'Request approved.');
    }

    public function generate(Request $request, BatchRequest $batchRequest, DistributionBatchGenerator $generator): RedirectResponse
    {
        $data = $this->validateGenerationSettings($request, $batchRequest);

        $batch = $generator->generate($batchRequest, $request->user(), $data);

        return redirect()
            ->route('admin.batch-requests.index')
            ->with('status', 'Batch generated: '.$batch->batch_id);
    }

    public function markSent(Request $request, BatchRequest $batchRequest): RedirectResponse
    {
        $data = $request->validate([
            'delivery_document' => ['nullable', 'file', 'max:10240'],
        ]);

        return $this->updateDeliveryState(
            $request,
            $batchRequest,
            BatchRequest::STATUS_SENT,
            'request_sent',
            'Request marked as sent.',
            $data
        );
    }

    public function uploadDocument(Request $request, BatchRequest $batchRequest): RedirectResponse
    {
        if (! $batchRequest->batch) {
            return back()->withErrors('Generate a batch before uploading a delivery document.');
        }

        $data = $request->validate([
            'delivery_document' => ['required', 'file', 'max:10240'],
        ]);

        $path = $this->storeDeliveryDocument($data);
        $batch = $batchRequest->batch;

        $batchRequest->update(['delivery_document_path' => $path]);
        if ($batch) {
            $batch->update(['delivery_document_path' => $path]);
        }

        ActivityLog::create([
            'account_id' => $batchRequest->account_id,
            'actor_id' => $request->user()->id,
            'batch_id' => $batch?->batch_id,
            'batch_request_id' => $batchRequest->id,
            'event' => 'delivery_document_uploaded',
            'description' => 'Delivery document uploaded for request #'.$batchRequest->id.'.',
        ]);

        return back()->with('status', 'Delivery document uploaded.');
    }

    public function complete(Request $request, BatchRequest $batchRequest): RedirectResponse
    {
        if ($batchRequest->status !== BatchRequest::STATUS_SENT) {
            return back()->withErrors('Only sent requests can be completed.');
        }

        return $this->updateDeliveryState(
            $request,
            $batchRequest,
            BatchRequest::STATUS_COMPLETED,
            'request_completed',
            'Request marked as completed.'
        );
    }

    private function validateGenerationSettings(Request $request, BatchRequest $batchRequest): array
    {
        if ($batchRequest->product_type === BatchRequest::PRODUCT_ESIM) {
            return $request->validate([
                'esim_type_id' => ['required', 'integer', 'exists:esim_types,id'],
                'label' => ['nullable', 'string', 'max:255'],
            ]);
        }

        return $request->validate([
            'prefix' => ['required', 'string', 'max:50'],
            'total_minutes' => ['required', 'integer', 'min:1', 'max:100000'],
            'name_prefix' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function updateDeliveryState(
        Request $request,
        BatchRequest $batchRequest,
        string $status,
        string $event,
        string $flashMessage,
        array $data = []
    ): RedirectResponse {
        if (! $batchRequest->batch) {
            return back()->withErrors('Generate a batch before updating delivery status.');
        }

        if ($batchRequest->status === BatchRequest::STATUS_COMPLETED && $status !== BatchRequest::STATUS_COMPLETED) {
            return back()->withErrors('Completed requests cannot be moved back.');
        }

        $path = isset($data['delivery_document'])
            ? $this->storeDeliveryDocument($data)
            : $batchRequest->delivery_document_path;

        DB::transaction(function () use ($request, $batchRequest, $status, $event, $path): void {
            $timestampColumn = $status === BatchRequest::STATUS_SENT ? 'sent_at' : 'completed_at';
            $now = now();
            $batch = Batch::whereKey($batchRequest->batch->id)->lockForUpdate()->firstOrFail();
            $batchRequest = BatchRequest::whereKey($batchRequest->id)->lockForUpdate()->firstOrFail();

            $batchRequest->update([
                'status' => $status,
                $timestampColumn => $now,
                'delivery_document_path' => $path,
            ]);

            $batch->update([
                'status' => $status === BatchRequest::STATUS_SENT ? Batch::STATUS_SENT : Batch::STATUS_COMPLETED,
                $timestampColumn => $now,
                'delivery_document_path' => $path,
            ]);

            ActivityLog::create([
                'account_id' => $batchRequest->account_id,
                'actor_id' => $request->user()->id,
                'batch_id' => $batch->batch_id,
                'batch_request_id' => $batchRequest->id,
                'event' => $event,
                'description' => $batchRequest->productLabel().' request #'.$batchRequest->id.' is now '.$status.'.',
                'metadata' => [
                    'product_type' => $batchRequest->product_type,
                    'quantity' => $batchRequest->quantity,
                ],
            ]);
        });

        return back()->with('status', $flashMessage);
    }

    private function storeDeliveryDocument(array $data): string
    {
        return $data['delivery_document']->store('delivery-documents', 'public');
    }
}
