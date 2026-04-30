@extends('account.layout')

@section('page-title', 'Settings')

@section('content')
    @php
        $profilePhotoUrl = $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : null;
    @endphp

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="account-form-panel account-form-panel-wide">
            <h2 class="account-panel-title">Profile Information</h2>
            <p class="account-panel-description">Update your account name, email address, and profile photo.</p>

            <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                @method('PATCH')

                <div class="profile-photo-control">
                    <span class="profile-avatar profile-photo-preview">
                        @if($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="" class="profile-avatar-image">
                        @else
                            {{ strtoupper(substr($user->name ?? 'AF', 0, 1)) }}
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">
                        <label for="profile_photo" class="account-form-label">Profile Photo</label>
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="account-form-control account-file-control">
                        <p class="account-form-help mt-2">JPG, PNG, GIF, or WebP up to 2 MB.</p>
                        @error('profile_photo')
                            <p class="account-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="name" class="account-form-label">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="account-form-control">
                    @error('name')
                        <p class="account-form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="account-form-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="account-form-control">
                    @error('email')
                        <p class="account-form-error">{{ $message }}</p>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="account-warning-callout mt-3">
                            <p class="font-semibold">Your email address is unverified.</p>
                            <button form="send-verification" class="mt-2">Resend verification email</button>
                        </div>
                    @endif
                </div>

                <div class="account-form-actions account-form-divider">
                    <button type="submit" class="neon-primary-button">Save Changes</button>
                    @if (session('status') === 'profile-updated')
                        <span class="account-form-success">Saved.</span>
                    @endif
                </div>
            </form>
        </section>

        <section class="account-form-panel account-form-panel-wide">
            <h2 class="account-panel-title">Update Password</h2>
            <p class="account-panel-description">Keep your distributor account password current.</p>

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="account-form-label">Current Password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="account-form-control">
                    @foreach ($errors->updatePassword->get('current_password') as $message)
                        <p class="account-form-error">{{ $message }}</p>
                    @endforeach
                </div>

                <div>
                    <label for="password" class="account-form-label">New Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="account-form-control">
                    @foreach ($errors->updatePassword->get('password') as $message)
                        <p class="account-form-error">{{ $message }}</p>
                    @endforeach
                </div>

                <div>
                    <label for="password_confirmation" class="account-form-label">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="account-form-control">
                    @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                        <p class="account-form-error">{{ $message }}</p>
                    @endforeach
                </div>

                <div class="account-form-actions account-form-divider">
                    <button type="submit" class="neon-primary-button">Update Password</button>
                    @if (session('status') === 'password-updated')
                        <span class="account-form-success">Saved.</span>
                    @endif
                </div>
            </form>
        </section>

        <section class="account-form-panel account-form-panel-wide account-form-panel-danger xl:col-span-2">
            <h2 class="account-panel-title text-rose-200">Delete Account</h2>
            <p class="account-panel-description">Once deleted, this distributor account and its access will be permanently removed.</p>

            <form method="POST" action="{{ route('profile.destroy') }}" class="mt-5 grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                @csrf
                @method('DELETE')

                <div>
                    <label for="delete_password" class="account-form-label">Password</label>
                    <input id="delete_password" name="password" type="password" class="account-form-control">
                    @foreach ($errors->userDeletion->get('password') as $message)
                        <p class="account-form-error">{{ $message }}</p>
                    @endforeach
                </div>

                <button type="submit" class="account-danger-button">Delete Account</button>
            </form>
        </section>
    </div>
@endsection
