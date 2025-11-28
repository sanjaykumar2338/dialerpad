<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DialerPad Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            color-scheme: dark;
            --sidebar-bg: linear-gradient(180deg, #0f172a 0%, #0f172a 55%, #1e3a8a 100%);
            --accent: #f97316;
            --accent-muted: rgba(249, 115, 22, 0.15);
            --success: #10b981;
            --danger: #ef4444;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #020617;
            color: #e2e8f0;
        }
        a {
            color: inherit;
        }
        .admin-shell {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: radial-gradient(circle at 30% 30%, #fef3c7, #f97316);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #0f172a;
        }
        .brand h1 {
            font-size: 1.2rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .nav-links a {
            padding: 0.85rem 1rem;
            border-radius: 0.9rem;
            color: #cbd5f5;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }
        .nav-links a.active,
        .nav-links a:hover {
            background: var(--accent-muted);
            color: #fff;
        }
        .nav-section-label {
            font-size: 0.8rem;
            letter-spacing: 0.12em;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
        }
        .content-area {
            flex: 1;
            padding: 2rem 3rem;
            background: radial-gradient(circle at top, rgba(15, 118, 110, 0.15), transparent 45%) #030712;
        }
        header.admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        header.admin-header span {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .alert {
            border-radius: 0.85rem;
            padding: 0.85rem 1rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #d1fae5;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fecaca;
        }
        main {
            background: rgba(2, 6, 23, 0.7);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 40px 80px rgba(2, 6, 23, 0.5);
        }
        .table {
            color: #e2e8f0;
        }
        .table thead {
            color: #f8fafc;
            border-bottom: 1px solid rgba(148, 163, 184, 0.3);
        }
        .table tbody tr {
            border-color: rgba(148, 163, 184, 0.1);
        }
        label,
        .form-label {
            color: #f8fafc;
        }
        .form-control,
        .form-select,
        textarea.form-control {
            background-color: rgba(15, 23, 42, 0.8);
            border-color: rgba(148, 163, 184, 0.4);
            color: #f8fafc;
        }
        .form-control:focus,
        .form-select:focus {
            background-color: rgba(15, 23, 42, 0.95);
            border-color: var(--accent);
            box-shadow: none;
            color: #fff;
        }
        .form-control::placeholder {
            color: rgba(248, 250, 252, 0.6);
        }
        @media (max-width: 960px) {
            .admin-shell {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .nav-links {
                flex-direction: row;
                flex-wrap: wrap;
            }
            .content-area {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @php
        $currentUser = auth()->user();
    @endphp
    <div class="admin-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">DP</div>
                <h1>DialerPad</h1>
            </div>
            <div>
                <nav class="nav-links">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('admin.call-cards.index') }}" class="{{ request()->routeIs('admin.call-cards.*') ? 'active' : '' }}">Call Cards</a>
                    <a href="{{ route('admin.call-sessions.index') }}" class="{{ request()->routeIs('admin.call-sessions.*') ? 'active' : '' }}">Call Sessions</a>
                    <a href="{{ route('admin.esim-types.index') }}" class="{{ request()->routeIs('admin.esim-types.*') || request()->routeIs('admin.esim-requests.*') || request()->routeIs('admin.esim-codes.*') ? 'active' : '' }}">eSIM Types</a>
                    <a href="{{ route('admin.esim-codes.index') }}" class="{{ request()->routeIs('admin.esim-codes.*') ? 'active' : '' }}">eSIM QR Codes</a>
                    <a href="{{ route('admin.esim-requests.index') }}" class="{{ request()->routeIs('admin.esim-requests.*') ? 'active' : '' }}">eSIM Requests</a>
                </nav>
            </div>
        </aside>
        <div class="content-area">
            <header class="admin-header">
                <div>
                    <h2 style="margin:0;">@yield('page-title', 'Overview')</h2>
                    <span>Secure admin workspace</span>
                </div>
                @if($currentUser)
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $currentUser->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </header>
            <main>
                @yield('content')
            </main>
        </div>
    </div>
    @php
        $statusMessages = [
            'profile-updated' => 'Profile updated successfully.',
            'password-updated' => 'Password updated successfully.',
            'verification-link-sent' => 'Verification link sent to your email.',
        ];
    @endphp
    @if ($rawStatus = session('status'))
        @php($toastMessage = $statusMessages[$rawStatus] ?? $rawStatus)
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json($toastMessage),
                timer: 4000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: `{!! implode('<br>', $errors->all()) !!}`,
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form.delete-form').forEach((form) => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Delete this record?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#d33',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
