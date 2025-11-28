@extends('admin.layout')

@section('page-title', 'eSIM Types')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">eSIM Types</h2>
            <small class="text-muted">Plans you can assign to incoming requests.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.esim-requests.index') }}" class="btn btn-outline-light">Requests</a>
            <a href="{{ route('admin.esim-types.create') }}" class="btn btn-primary">Add Type</a>
        </div>
    </div>

    <div class="card bg-transparent border-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Provider Ref</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($types as $type)
                        <tr>
                            <td class="fw-semibold">{{ $type->name }}</td>
                            <td>{{ $type->provider_reference_code ?? '—' }}</td>
                            <td style="max-width: 320px;">{{ \Illuminate\Support\Str::limit($type->description, 120) ?: '—' }}</td>
                            <td>{{ $type->created_at->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.esim-types.edit', $type) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                <form action="{{ route('admin.esim-types.destroy', $type) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No eSIM types found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $types->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
