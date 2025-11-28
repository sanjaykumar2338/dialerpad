@extends('admin.layout')

@section('page-title', 'Edit Call Card')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card bg-transparent border-light">
                <div class="card-body">
                    <form action="{{ route('admin.call-cards.update', $card) }}" method="POST" class="row g-3 text-white">
                        @csrf
                        @method('PUT')

                        <div class="col-md-6">
                            <label for="name" class="form-label text-white">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $card->name) }}"
                                   class="form-control text-white"
                                   style="background-color: rgba(15,23,42,0.9); border-color: rgba(148,163,184,0.6);" required>
                        </div>

                        <div class="col-md-6">
                            <label for="prefix" class="form-label text-white">Prefix</label>
                            <input type="text" id="prefix" name="prefix" value="{{ old('prefix', $card->prefix) }}"
                                   class="form-control text-white"
                                   style="background-color: rgba(15,23,42,0.9); border-color: rgba(148,163,184,0.6);" required>
                        </div>

                        <div class="col-md-6">
                            <label for="total_minutes" class="form-label text-white">Total Minutes</label>
                            <input type="number" id="total_minutes" name="total_minutes" value="{{ old('total_minutes', $card->total_minutes) }}" min="1"
                                   class="form-control text-white"
                                   style="background-color: rgba(15,23,42,0.9); border-color: rgba(148,163,184,0.6);" required>
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label text-white">Notes</label>
                            <textarea id="notes" name="notes" rows="4"
                                      class="form-control text-white"
                                      style="background-color: rgba(15,23,42,0.9); border-color: rgba(148,163,184,0.6);">{{ old('notes', $card->notes) }}</textarea>
                        </div>

                        <div class="col-12 d-flex gap-3">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('admin.call-cards.index') }}" class="btn btn-outline-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card bg-transparent border-light h-100 text-white">
                <div class="card-body text-center">
                    <h5 class="mb-3 text-white">QR Preview</h5>
                    <img src="{{ asset($card->qr_code_path) }}" alt="QR {{ $card->name }}" class="img-fluid rounded" style="max-width: 180px;">
                </div>
            </div>
        </div>
    </div>
@endsection
