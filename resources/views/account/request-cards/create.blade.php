@extends('account.layout')

@section('page-title', 'Request Cards')

@section('content')
    @php
        $statusLabel = fn ($value) => $value ? str_replace('_', ' ', ucwords($value, '_')) : '-';
        $modalShouldOpen = $errors->has('product_type') || $errors->has('quantity') || $errors->has('notes');
    @endphp

    <div
        x-data="{ createOpen: {{ $modalShouldOpen ? 'true' : 'false' }} }"
        x-init="
            const params = new URLSearchParams(window.location.search);
            if (params.get('open') === 'create') {
                createOpen = true;
                params.delete('open');
                const query = params.toString();
                window.history.replaceState({}, document.title, window.location.pathname + (query ? '?' + query : '') + window.location.hash);
            }
        "
        x-on:keydown.escape.window="createOpen = false"
    >
        <section class="glass-panel batch-panel">
            <div class="panel-header">
                <div>
                    <h2>Submitted Requests</h2>
                    <p>Pending and processed card requests. Submit a request for new eSIM or call card stock. Admin will review and generate a batch after approval.</p>
                </div>
                <button type="button" class="neon-primary-button" x-on:click="createOpen = true">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M10 4v12M4 10h12"></path>
                    </svg>
                    <span>Create Request</span>
                </button>
            </div>

            <div class="neon-table-wrap">
                <table class="neon-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            @if(auth()->user()?->is_admin)
                                <th>Account</th>
                            @endif
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Submitted Date</th>
                            <th>Notes</th>
                            <th>Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batchRequests as $batchRequest)
                            <tr>
                                <td class="batch-id">#{{ $batchRequest->id }}</td>
                                @if(auth()->user()?->is_admin)
                                    <td>{{ $batchRequest->account?->name ?? 'Deleted account' }}</td>
                                @endif
                                <td>{{ $batchRequest->productLabel() }}</td>
                                <td>{{ number_format($batchRequest->quantity) }}</td>
                                <td>
                                    <span class="status-badge status-{{ $batchRequest->status }}">{{ $statusLabel($batchRequest->status) }}</span>
                                </td>
                                <td>{{ $batchRequest->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $batchRequest->notes ? \Illuminate\Support\Str::limit($batchRequest->notes, 90) : '-' }}</td>
                                <td>
                                    @if($batchRequest->batch)
                                        <span class="status-badge status-generated">Batch #{{ $batchRequest->batch->batch_id }}</span>
                                    @else
                                        <span class="batch-pending-label" title="Batch will be created after admin approval and generation">Not generated yet</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()?->is_admin ? 8 : 7 }}" class="empty-state">
                                    <div class="request-empty-state">
                                        <p>No requests submitted yet.</p>
                                        <button type="button" class="neon-primary-button" x-on:click="createOpen = true">
                                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="M10 4v12M4 10h12"></path>
                                            </svg>
                                            <span>Create Request</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($batchRequests->hasPages())
                <div class="account-pagination">
                    {{ $batchRequests->links() }}
                </div>
            @endif
        </section>

        <div
            id="createRequestModal"
            x-show="createOpen"
            x-transition.opacity
            x-cloak
            class="account-modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-request-title"
        >
            <div class="account-modal" x-on:click.outside="createOpen = false">
                <div class="account-modal-header">
                    <div>
                        <h2 id="create-request-title">Create Request</h2>
                        <p>Submit a new batch request for approval.</p>
                    </div>
                    <button type="button" class="account-modal-close" x-on:click="createOpen = false" aria-label="Close modal">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 5l10 10M15 5 5 15"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('account.requests.store') }}" method="POST" class="account-modal-body space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="product_type" class="account-form-label">Product Type</label>
                            <select id="product_type" name="product_type" required class="account-form-select">
                                <option value="">Select product</option>
                                <option value="esim" @selected(old('product_type') === 'esim')>eSIM</option>
                                <option value="call_card" @selected(old('product_type') === 'call_card')>Call card</option>
                            </select>
                            @error('product_type')
                                <p class="account-form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="quantity" class="account-form-label">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="1000" value="{{ old('quantity', 100) }}" required class="account-form-control">
                            @error('quantity')
                                <p class="account-form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="account-form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="5" class="account-form-textarea">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="account-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="account-form-actions account-form-divider flex-col-reverse sm:flex-row">
                        <button type="button" class="neon-secondary-button w-full sm:w-auto" x-on:click="createOpen = false">Cancel</button>
                        <button type="submit" class="neon-primary-button w-full sm:w-auto">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
