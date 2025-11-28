@extends('admin.layout')

@section('page-title', 'Profile')

@section('content')
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card bg-transparent border-light h-100">
                <div class="card-body">
                    <h5 class="card-title text-white">Profile Information</h5>
                    <p class="text-muted mb-4">Update your personal details and email address.</p>

                    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="POST" action="{{ route('profile.update') }}" class="row g-3">
                        @csrf
                        @method('PATCH')

                        <div class="col-12">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-3">
                                    <p class="text-warning small mb-2">Your email address is unverified.</p>
                                    <button form="send-verification" class="btn btn-sm btn-outline-light">Resend verification email</button>
                                </div>
                            @endif
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card bg-transparent border-light h-100">
                <div class="card-body">
                    <h5 class="card-title text-white">Update Password</h5>
                    <p class="text-muted mb-4">Keep your account secure with a strong password.</p>

                    <form method="POST" action="{{ route('password.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-12">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
                            @foreach ($errors->updatePassword->get('current_password') as $message)
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endforeach
                        </div>

                        <div class="col-12">
                            <label for="password" class="form-label">New Password</label>
                            <input id="password" name="password" type="password" class="form-control" autocomplete="new-password">
                            @foreach ($errors->updatePassword->get('password') as $message)
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endforeach
                        </div>

                        <div class="col-12">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                            @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endforeach
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Update password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card bg-transparent border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">Delete Account</h5>
                    <p class="text-muted">Once deleted, all resources and data will be permanently removed.</p>

                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-dark" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteAccountModalLabel">Delete Account</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>Enter your password to confirm permanent account deletion.</p>
                        <div class="mb-3">
                            <label for="delete_password" class="form-label">Password</label>
                            <input id="delete_password" name="password" type="password" class="form-control" placeholder="Password">
                            @foreach ($errors->userDeletion->get('password') as $message)
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($errors->userDeletion->any())
        <script>
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
            deleteModal.show();
        </script>
    @endif
@endsection
