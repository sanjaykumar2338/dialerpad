@extends('admin.layout')

@section('page-title', 'Distribution Requests')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">Distribution Requests</h2>
            <small class="text-muted">Approve, generate, send, and complete account card batches.</small>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.batch-requests.index') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="product_type" class="form-label">Product</label>
            <select id="product_type" name="product_type" class="form-select">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product }}" @selected($filters['product_type'] === $product)>{{ $product === 'esim' ? 'eSIM' : 'Call card' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="search" class="form-label">Search</label>
            <input type="text" id="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Account name, email, or request ID">
        </div>
        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="d-grid gap-3">
        @forelse ($requests as $batchRequest)
            @php
                $statusClass = match ($batchRequest->status) {
                    'pending' => 'bg-warning text-dark',
                    'approved' => 'bg-primary',
                    'generated' => 'bg-success',
                    'sent' => 'bg-info text-dark',
                    'completed' => 'bg-secondary',
                    default => 'bg-light text-dark',
                };
            @endphp
            <div class="card bg-transparent border-light">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h3 class="h5 mb-0">Request #{{ $batchRequest->id }}</h3>
                                <span class="badge {{ $statusClass }}">{{ ucfirst($batchRequest->status) }}</span>
                            </div>
                            <div class="text-muted small mt-1">
                                {{ $batchRequest->account?->name ?? 'Deleted account' }} - {{ $batchRequest->account?->email ?? 'No email' }}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">{{ $batchRequest->productLabel() }} x {{ number_format($batchRequest->quantity) }}</div>
                            <div class="text-muted small">{{ $batchRequest->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>

                    @if($batchRequest->notes)
                        <div class="mb-3 rounded p-3" style="background: rgba(148, 163, 184, 0.12);">
                            {{ $batchRequest->notes }}
                        </div>
                    @endif

                    @if($batchRequest->batch)
                        <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                            <span class="text-muted small">Batch:</span>
                            <code class="text-light">{{ $batchRequest->batch->batch_id }}</code>
                            <a href="{{ route('admin.distribution-batches.download', $batchRequest->batch) }}" class="btn btn-sm btn-outline-light">Download ZIP</a>
                            @if($batchRequest->delivery_document_path)
                                <a href="{{ asset('storage/' . $batchRequest->delivery_document_path) }}" target="_blank" class="btn btn-sm btn-outline-light">Delivery Document</a>
                            @endif
                        </div>
                    @endif

                    <div class="d-flex flex-column gap-3">
                        @if($batchRequest->status === 'pending')
                            <form action="{{ route('admin.batch-requests.approve', $batchRequest) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">Approve Request</button>
                            </form>
                        @endif

                        @if($batchRequest->status === 'approved')
                            <form action="{{ route('admin.batch-requests.generate', $batchRequest) }}" method="POST" class="row g-3 align-items-end">
                                @csrf
                                @if($batchRequest->product_type === 'esim')
                                    @if($esimTypes->isNotEmpty())
                                        <div class="col-md-5">
                                            <label class="form-label" for="esim_type_id_{{ $batchRequest->id }}">eSIM Plan</label>
                                            <select id="esim_type_id_{{ $batchRequest->id }}" name="esim_type_id" class="form-select" required>
                                                <option value="">Select plan</option>
                                                @foreach($esimTypes as $type)
                                                    <option value="{{ $type->id }}" @selected(old('esim_type_id') == $type->id)>{{ $type->name }} @if($type->product_id) - {{ $type->product_id }} @endif</option>
                                                @endforeach
                                            </select>
                                            <div class="form-text text-muted">Only active eSIM plans with a product ID are listed.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="label_{{ $batchRequest->id }}">Label</label>
                                            <input id="label_{{ $batchRequest->id }}" type="text" name="label" class="form-control" value="{{ old('label', 'Request #'.$batchRequest->id) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-success w-100">Generate</button>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <div class="alert alert-warning mb-0">
                                                No eSIM plans available. Please create a plan first.
                                                <a href="{{ route('admin.esim-types.create') }}" class="alert-link">Create plan</a>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="col-md-3">
                                        <label class="form-label" for="prefix_{{ $batchRequest->id }}">Dial Prefix</label>
                                        <input id="prefix_{{ $batchRequest->id }}" type="text" name="prefix" class="form-control" value="{{ old('prefix', config('pbx.dial_prefix_default', '223')) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="total_minutes_{{ $batchRequest->id }}">Call Card Minutes</label>
                                        <input id="total_minutes_{{ $batchRequest->id }}" type="number" name="total_minutes" min="1" class="form-control" value="{{ old('total_minutes') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="name_prefix_{{ $batchRequest->id }}">Name Prefix</label>
                                        <input id="name_prefix_{{ $batchRequest->id }}" type="text" name="name_prefix" class="form-control" value="{{ old('name_prefix', 'Request '.$batchRequest->id) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-success w-100">Generate</button>
                                    </div>
                                @endif
                            </form>
                        @endif

                        @if($batchRequest->status === 'generated')
                            <form action="{{ route('admin.batch-requests.sent', $batchRequest) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                                @csrf
                                <div class="col-md-7">
                                    <label class="form-label" for="delivery_document_sent_{{ $batchRequest->id }}">Signed Delivery Document</label>
                                    <input id="delivery_document_sent_{{ $batchRequest->id }}" type="file" name="delivery_document" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-info w-100">Mark Sent</button>
                                </div>
                            </form>
                        @endif

                        @if(in_array($batchRequest->status, ['generated', 'sent', 'completed'], true))
                            <form action="{{ route('admin.batch-requests.document', $batchRequest) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                                @csrf
                                <div class="col-md-7">
                                    <label class="form-label" for="delivery_document_{{ $batchRequest->id }}">Upload Delivery Document</label>
                                    <input id="delivery_document_{{ $batchRequest->id }}" type="file" name="delivery_document" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-outline-light w-100">Upload</button>
                                </div>
                            </form>
                        @endif

                        @if($batchRequest->status === 'sent')
                            <form action="{{ route('admin.batch-requests.complete', $batchRequest) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-secondary">Mark Completed</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">No batch requests found.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>
@endsection
