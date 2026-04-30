<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BatchRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BatchRequestController extends Controller
{
    public function create(Request $request): View
    {
        $batchRequests = BatchRequest::with(['account', 'batch'])
            ->when(! $request->user()->is_admin, fn ($query) => $query->where('account_id', $request->user()->id))
            ->latest()
            ->paginate(12);

        return view('account.request-cards.create', [
            'batchRequests' => $batchRequests,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_type' => ['required', Rule::in(BatchRequest::PRODUCTS)],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $batchRequest = BatchRequest::create([
            'account_id' => $request->user()->id,
            'product_type' => $data['product_type'],
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? null,
            'status' => BatchRequest::STATUS_PENDING,
        ]);

        ActivityLog::create([
            'account_id' => $request->user()->id,
            'actor_id' => $request->user()->id,
            'batch_request_id' => $batchRequest->id,
            'event' => 'request_generated',
            'description' => $batchRequest->productLabel().' request for '.number_format($batchRequest->quantity).' cards was submitted.',
            'metadata' => [
                'product_type' => $batchRequest->product_type,
                'quantity' => $batchRequest->quantity,
            ],
        ]);

        return redirect()
            ->route('account.requests.create')
            ->with('status', 'Card request submitted.');
    }
}
