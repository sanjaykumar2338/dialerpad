@extends('admin.layout')

@section('page-title', 'Distributors')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">Distributors</h2>
            <small class="text-muted">Create distributor logins and manage account access.</small>
        </div>
        <a href="{{ route('admin.distributors.create') }}" class="btn btn-primary">Create Distributor</a>
    </div>

    <form method="GET" action="{{ route('admin.distributors.index') }}" class="row g-3 mb-4">
        <div class="col-md-5">
            <label for="search" class="form-label">Search</label>
            <input id="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Name, email, phone, or company">
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button class="btn btn-outline-light" type="submit">Filter</button>
            <a href="{{ route('admin.distributors.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card bg-transparent border-0">
        <div class="table-responsive">
            <table class="table align-middle table-dark table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($distributors as $distributor)
                        <tr>
                            <td class="fw-semibold">{{ $distributor->name }}</td>
                            <td>{{ $distributor->email }}</td>
                            <td>{{ $distributor->phone ?: '-' }}</td>
                            <td>{{ $distributor->company_name ?: '-' }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($distributor->role)) }}</td>
                            <td>
                                <span class="badge {{ $distributor->status === \App\Models\User::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($distributor->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('admin.distributors.status', $distributor) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $distributor->status === \App\Models\User::STATUS_ACTIVE ? \App\Models\User::STATUS_INACTIVE : \App\Models\User::STATUS_ACTIVE }}">
                                    <button type="submit" class="btn btn-sm btn-outline-warning me-2">
                                        {{ $distributor->status === \App\Models\User::STATUS_ACTIVE ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.distributors.edit', $distributor) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                <form action="{{ route('admin.distributors.destroy', $distributor) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No distributors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $distributors->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
