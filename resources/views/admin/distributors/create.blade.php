@extends('admin.layout')

@section('page-title', 'Create Distributor')

@section('content')
    <div class="card bg-transparent border-light">
        <div class="card-body">
            <form action="{{ route('admin.distributors.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone optional</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>

                <div class="col-md-6">
                    <label for="company_name" class="form-label">Company name optional</label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" class="form-control">
                </div>

                <div class="col-md-6">
                    <label for="role" class="form-label">Role/type</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="{{ \App\Models\User::ROLE_DISTRIBUTOR }}" @selected(old('role', \App\Models\User::ROLE_DISTRIBUTOR) === \App\Models\User::ROLE_DISTRIBUTOR)>Distributor</option>
                        <option value="{{ \App\Models\User::ROLE_MASTER_DISTRIBUTOR }}" @selected(old('role') === \App\Models\User::ROLE_MASTER_DISTRIBUTOR)>Master Distributor</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="{{ \App\Models\User::STATUS_ACTIVE }}" @selected(old('status', \App\Models\User::STATUS_ACTIVE) === \App\Models\User::STATUS_ACTIVE)>Active</option>
                        <option value="{{ \App\Models\User::STATUS_INACTIVE }}" @selected(old('status') === \App\Models\User::STATUS_INACTIVE)>Inactive</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <div class="col-12 d-flex gap-3">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a href="{{ route('admin.distributors.index') }}" class="btn btn-outline-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
