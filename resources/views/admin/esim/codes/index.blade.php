@extends('admin.layout')

@section('page-title', 'eSIM QR Codes')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">eSIM QR Codes</h2>
            <small class="text-muted">Each code points to a specific plan at /esim/{uuid}.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.esim-requests.index') }}" class="btn btn-outline-light">Requests</a>
            <a href="{{ route('admin.esim-types.index') }}" class="btn btn-outline-light">Types</a>
            <a href="{{ route('admin.esim-codes.create') }}" class="btn btn-primary">Create QR</a>
        </div>
    </div>

    <div class="card bg-transparent border-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Plan</th>
                        <th>Product ID</th>
                        <th>UUID / Link</th>
                        <th>Status</th>
                        <th>QR</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($codes as $code)
                        <tr>
                            <td>{{ $code->label ?? '—' }}</td>
                            <td>{{ $code->type?->name ?? '—' }}</td>
                            <td>{{ $code->product_id ?? '—' }}</td>
                            <td class="text-break">
                                <div class="small text-muted">{{ $code->uuid }}</div>
                                <a href="{{ url('/esim/' . $code->uuid) }}" target="_blank" class="text-emerald-300 small">Open</a>
                            </td>
                            <td>
                                <span class="badge
                                    @if($code->status === 'unused') bg-success
                                    @elseif($code->status === 'used') bg-secondary
                                    @else bg-warning text-dark
                                    @endif">
                                    {{ ucfirst($code->status) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $qrPath = 'storage/esim-qrcodes/' . $code->uuid . '.png';
                                @endphp
                                <img src="{{ asset($qrPath) }}" alt="QR {{ $code->uuid }}" style="width:70px; height:70px;" class="rounded border border-light">
                            </td>
                            <td>{{ $code->created_at->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.esim-codes.edit', $code) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                <form action="{{ route('admin.esim-codes.destroy', $code) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No eSIM codes yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $codes->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
