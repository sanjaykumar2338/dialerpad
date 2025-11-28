@extends('admin.layout')

@section('page-title', 'Call Cards')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">Call Cards</h2>
            <small class="text-muted">Manage inventory, export QR packs, and control limits.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.call-cards.create') }}" class="btn btn-primary">Create Card</a>
            <a href="{{ route('admin.call-cards.export') }}" class="btn btn-outline-light">Export QR ZIP</a>
        </div>
    </div>

    <div class="card bg-transparent border-0">
        <div class="table-responsive">
            <table class="table align-middle table-dark table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Prefix</th>
                        <th>Total Minutes</th>
                        <th>Used</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th>QR</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cards as $card)
                        <tr>
                            <td class="fw-semibold">{{ $card->name }}</td>
                            <td>{{ $card->prefix }}</td>
                            <td>{{ $card->total_minutes }}</td>
                            <td>{{ $card->used_minutes }}</td>
                            <td>{{ $card->remaining_minutes }}</td>
                            <td>
                                <span class="badge {{ $card->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($card->status) }}
                                </span>
                            </td>
                            <td>
                                <img src="{{ asset($card->qr_code_path) }}" alt="QR {{ $card->name }}" class="rounded" style="width:70px; height:70px;">
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.call-cards.show', $card) }}" class="btn btn-sm btn-outline-light me-2">View</a>
                                <a href="{{ route('admin.call-sessions.index', ['card_id' => $card->id]) }}" class="btn btn-sm btn-outline-light me-2">View Sessions</a>
                                <a href="{{ route('admin.call-cards.edit', $card) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                <form action="{{ route('admin.call-cards.destroy', $card) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No call cards found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $cards->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
