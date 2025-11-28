@extends('admin.layout')

@section('page-title', 'Edit eSIM QR')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">Edit eSIM QR</h2>
            <small class="text-muted">{{ $code->uuid }}</small>
        </div>
        <div>
            <a href="{{ route('admin.esim-codes.index') }}" class="btn btn-outline-light">Back to list</a>
        </div>
    </div>

    <div class="card bg-transparent border-light">
        <div class="card-body">
            <form action="{{ route('admin.esim-codes.update', $code) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="esim_type_id" class="form-label">Plan</label>
                    <select name="esim_type_id" id="esim_type_id" class="form-select" required>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected($code->esim_type_id == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="label" class="form-label">Label (optional)</label>
                    <input type="text" id="label" name="label" value="{{ old('label', $code->label) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="active" @selected($code->status === 'active')>Active</option>
                        <option value="disabled" @selected($code->status === 'disabled')>Disabled</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Public Link</label>
                    <div class="form-control bg-dark text-white">{{ url('/esim/' . $code->uuid) }}</div>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.esim-codes.index') }}" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection
