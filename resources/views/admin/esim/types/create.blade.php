@extends('admin.layout')

@section('page-title', 'Add eSIM Type')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0">Add eSIM Type</h2>
            <small class="text-muted">Configure a plan that requests can choose.</small>
        </div>
        <div>
            <a href="{{ route('admin.esim-types.index') }}" class="btn btn-outline-light">Back to list</a>
        </div>
    </div>

    <div class="card bg-transparent border-light">
        <div class="card-body">
            <form action="{{ route('admin.esim-types.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="product_id" class="form-label">Product ID (Mobimatter)</label>
                    <input type="text" name="product_id" id="product_id" value="{{ old('product_id') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="provider_reference_code" class="form-label">Provider Reference Code</label>
                    <input type="text" name="provider_reference_code" id="provider_reference_code" value="{{ old('provider_reference_code') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control" placeholder="Optional details">{{ old('description') }}</textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.esim-types.index') }}" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Type</button>
                </div>
            </form>
        </div>
    </div>
@endsection
