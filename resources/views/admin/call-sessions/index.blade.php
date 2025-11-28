@extends('admin.layout')

@section('page-title', 'Call Sessions')

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.call-sessions.export', request()->query()) }}" class="btn btn-outline-light">
            Export CSV
        </a>
    </div>

    <div class="card bg-transparent border-light mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.call-sessions.index') }}" class="row g-3 align-items-end">
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
                    <label for="card_id" class="form-label">Call Card</label>
                    <select name="card_id" id="card_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($cards as $id => $name)
                            <option value="{{ $id }}" @selected((string)$filters['card_id'] === (string)$id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Number</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Dialed or full number">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.call-sessions.index') }}" class="btn btn-outline-light w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-transparent border-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Card</th>
                        <th>Dialed Number</th>
                        <th>Duration (s)</th>
                        <th>Remaining (after call)</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $session->card?->name ?? 'N/A' }}</td>
                            <td>{{ $session->dialed_number }}</td>
                            <td>{{ $session->duration_seconds ?? '-' }}</td>
                            <td>{{ $session->remaining_minutes_after_call ?? $session->card?->remaining_minutes ?? '—' }}</td>
                            <td>
                                <span class="badge
                                    @if($session->status === 'completed') bg-success
                                    @elseif($session->status === 'failed') bg-danger
                                    @elseif($session->status === 'cancelled') bg-warning text-dark
                                    @else bg-info text-dark
                                    @endif">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </td>
                            <td>{{ $session->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No sessions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $sessions->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
