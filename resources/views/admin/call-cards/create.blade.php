@extends('admin.layout')

@section('page-title', 'Create Call Card')

@section('content')
    <div class="card bg-transparent border-light">
        <div class="card-body">
            <form action="{{ route('admin.call-cards.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" required>
                    <small class="text-muted">Base name for the card(s). For bulk, we will append #001, #002...</small>
                </div>

                <div class="col-md-6">
                    <label for="prefix" class="form-label">Prefix</label>
                    <input type="text" id="prefix" name="prefix" value="{{ old('prefix') }}" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="total_minutes" class="form-label">Total Minutes</label>
                    <input type="number" id="total_minutes" name="total_minutes" value="{{ old('total_minutes') }}" min="1" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" class="form-control" required>
                    <small class="text-muted">Create multiple cards at once (QR generated per card).</small>
                </div>

                <div class="col-md-4">
                    <label for="name_prefix" class="form-label">Name Prefix (optional)</label>
                    <input type="text" id="name_prefix" name="name_prefix" value="{{ old('name_prefix') }}" class="form-control">
                    <small class="text-muted">If set and quantity > 1, we'll use this as the base name.</small>
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="form-control">{{ old('notes') }}</textarea>
                </div>

                <div class="col-12 d-flex gap-3">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a href="{{ route('admin.call-cards.index') }}" class="btn btn-outline-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
