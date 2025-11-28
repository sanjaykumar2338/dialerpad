@extends('admin.layout')

@section('page-title', 'Manage eSIM Request')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0 text-white">Manage Request</h2>
            <small class="text-light">{{ $requestRecord->full_name }} • {{ $requestRecord->email }}</small>
        </div>
        <div>
            <a href="{{ route('admin.esim-requests.index') }}" class="btn btn-outline-light">Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card bg-transparent border-light">
                <div class="card-body">
                    <form action="{{ route('admin.esim-requests.update', $requestRecord) }}" method="POST" class="row g-3 text-white">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select text-white" style="background-color: rgba(15,23,42,0.9); border-color: rgba(148,163,184,0.6);" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($requestRecord->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="esim_type_id" class="form-label">eSIM Type</label>
                            <select name="esim_type_id" id="esim_type_id" class="form-select text-white" style="background-color: rgba(15,23,42,0.9); border-color: rgba(148,163,184,0.6);">
                                <option value="">Unassigned</option>
                                @foreach ($types as $id => $name)
                                    <option value="{{ $id }}" @selected((string)$requestRecord->esim_type_id === (string)$id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes / Provider Response</label>
                            <textarea name="notes" id="notes" rows="4" class="form-control text-white" placeholder="Add internal notes or provider response" style="background-color: rgba(15,23,42,0.9); border-color: rgba(148,163,184,0.6);">{{ old('notes', $requestRecord->notes) }}</textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.esim-requests.index') }}" class="btn btn-outline-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card bg-transparent border-light">
                <div class="card-body">
                    <h5 class="mb-3 text-white">Request Details</h5>
                    <div class="mb-2 text-white"><strong>Name:</strong> {{ $requestRecord->full_name }}</div>
                    <div class="mb-2 text-white"><strong>Email:</strong> {{ $requestRecord->email }}</div>
                    <div class="mb-2 text-white"><strong>Phone:</strong> {{ $requestRecord->phone ?? '—' }}</div>
                    <div class="mb-2 text-white"><strong>Device:</strong> {{ $requestRecord->device_model ?? '—' }}</div>
                    <div class="mb-2 text-white"><strong>Type:</strong> {{ $requestRecord->type?->name ?? '—' }}</div>
                    <div class="mb-2 text-white"><strong>Submitted:</strong> {{ $requestRecord->created_at->format('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
