@extends('admin.layout')

@section('page-title', 'Create eSIM QR')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">Create eSIM QR</h2>
            <small class="text-muted">Generate a QR tied to a specific plan.</small>
        </div>
        <div>
            <a href="{{ route('admin.esim-codes.index') }}" class="btn btn-outline-light">Back to list</a>
        </div>
    </div>

    <div class="card bg-transparent border-light">
        <div class="card-body">
            <form action="{{ route('admin.esim-codes.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="esim_type_id" class="form-label">Plan</label>
                    <select name="esim_type_id" id="esim_type_id" class="form-select" required>
                        <option value="">Select plan</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected(old('esim_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="label" class="form-label">Label (optional)</label>
                    <input type="text" id="label" name="label" value="{{ old('label') }}" class="form-control" placeholder="e.g. Promo July">
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.esim-codes.index') }}" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection
