@extends('admin.layout')

@section('page-title', 'eSIM Requests')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">eSIM Requests</h2>
            <small class="text-muted">Track submissions and update their status.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.esim-types.index') }}" class="btn btn-outline-light">Types</a>
        </div>
    </div>

    <div class="card bg-transparent border-light mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.esim-requests.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type_id" class="form-label">eSIM Type</label>
                    <select name="type_id" id="type_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($types as $id => $name)
                            <option value="{{ $id }}" @selected((string)$filters['type_id'] === (string)$id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Name, email or phone">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.esim-requests.index') }}" class="btn btn-outline-light w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-transparent border-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Device</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $requestRecord)
                        <tr>
                            <td class="fw-semibold">{{ $requestRecord->full_name }}</td>
                            <td>{{ $requestRecord->email }}</td>
                            <td>{{ $requestRecord->phone ?? '—' }}</td>
                            <td>{{ $requestRecord->device_model ?? '—' }}</td>
                            <td>{{ $requestRecord->type?->name ?? '—' }}</td>
                            <td>
                                <span class="badge
                                    @if($requestRecord->status === 'processed') bg-success
                                    @elseif($requestRecord->status === 'failed') bg-danger
                                    @else bg-warning text-dark
                                    @endif">
                                    {{ ucfirst($requestRecord->status) }}
                                </span>
                            </td>
                            <td>{{ $requestRecord->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.esim-requests.edit', $requestRecord) }}" class="btn btn-sm btn-outline-primary me-2">Manage</a>
                                <form action="{{ route('admin.esim-requests.destroy', $requestRecord) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
