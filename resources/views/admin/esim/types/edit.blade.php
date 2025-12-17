@extends('admin.layout')

@section('page-title', 'Edit eSIM Type')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">Edit eSIM Type</h2>
            <small class="text-muted">{{ $type->name }}</small>
        </div>
        <div>
            <a href="{{ route('admin.esim-types.index') }}" class="btn btn-outline-light">Back to list</a>
        </div>
    </div>

    <div class="card bg-transparent border-light">
        <div class="card-body">
            <form action="{{ route('admin.esim-types.update', $type) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $type->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="product_id" class="form-label">Product ID (Mobimatter)</label>
                    <input type="text" name="product_id" id="product_id" value="{{ old('product_id', $type->product_id) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="provider_reference_code" class="form-label">Provider Reference Code</label>
                    <input type="text" name="provider_reference_code" id="provider_reference_code" value="{{ old('provider_reference_code', $type->provider_reference_code) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" @selected(old('status', $type->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $type->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control" placeholder="Optional details">{{ old('description', $type->description) }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.esim-types.index') }}" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Type</button>
                </div>
            </form>
        </div>
    </div>
@endsection
