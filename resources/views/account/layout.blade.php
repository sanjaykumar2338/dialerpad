<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Distribution') - {{ config('app.name', 'AFRITEL') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-white">
@php
    $currentUser = auth()->user();
    $profilePhotoUrl = $currentUser?->profile_photo_path ? asset('storage/'.$currentUser->profile_photo_path) : null;
    $navIconClass = 'h-5 w-5';
    $links = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard'), 'icon' => 'dashboard'],
        ['label' => 'Request Cards', 'route' => 'account.requests.create', 'active' => request()->routeIs('account.requests.*'), 'icon' => 'cards'],
        ['label' => 'My Batches', 'route' => 'account.batches.index', 'active' => request()->routeIs('account.batches.*'), 'icon' => 'batches'],
        ['label' => 'Reports', 'route' => 'account.reports.index', 'active' => request()->routeIs('account.reports.*'), 'icon' => 'reports'],
        ['label' => 'Settings', 'route' => 'account.settings.edit', 'active' => request()->routeIs('account.settings.*') || request()->routeIs('profile.*'), 'icon' => 'settings'],
    ];
@endphp
<div class="account-shell min-h-screen lg:grid lg:grid-cols-[250px_minmax(0,1fr)]">
    <aside class="account-sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="afritel-mark" aria-label="AFRITEL dashboard">
                <span class="afritel-wordmark">AFRITEL</span>
                <span class="afritel-signal" aria-hidden="true"></span>
            </a>
        </div>

        <nav class="sidebar-nav" aria-label="Account navigation">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="sidebar-link {{ $link['active'] ? 'is-active' : '' }}"
                    @if($link['active']) aria-current="page" @endif
                >
                    <span class="sidebar-link-icon" aria-hidden="true">
                        @switch($link['icon'])
                            @case('dashboard')
                                <svg class="{{ $navIconClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1.6"></rect>
                                    <rect x="14" y="3.5" width="6.5" height="6.5" rx="1.6"></rect>
                                    <rect x="3.5" y="14" width="6.5" height="6.5" rx="1.6"></rect>
                                    <rect x="14" y="14" width="6.5" height="6.5" rx="1.6"></rect>
                                </svg>
                                @break
                            @case('cards')
                                <svg class="{{ $navIconClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="5" y="3.5" width="12" height="17" rx="2.2"></rect>
                                    <path d="M9 7.5h4M9 11h4M9 14.5h2.5"></path>
                                    <path d="M17 7h2a2 2 0 0 1 2 2v8"></path>
                                </svg>
                                @break
                            @case('batches')
                                <svg class="{{ $navIconClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M8 9.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"></path>
                                    <path d="M16 9.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"></path>
                                    <path d="M4 21v-2a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v2"></path>
                                    <path d="M12 21v-2a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v2"></path>
                                </svg>
                                @break
                            @case('reports')
                                <svg class="{{ $navIconClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="4" y="3.5" width="16" height="17" rx="2.2"></rect>
                                    <path d="M8 16v-4M12 16V8M16 16v-6"></path>
                                </svg>
                                @break
                            @default
                                <svg class="{{ $navIconClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"></path>
                                    <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .2 1.7 1.7 0 0 0-1 1.55V21a2 2 0 0 1-4 0v-.1a1.7 1.7 0 0 0-1-1.55 1.7 1.7 0 0 0-1.9.34l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 3.6 15a1.7 1.7 0 0 0-.2-1 1.7 1.7 0 0 0-1.55-1H1.8a2 2 0 0 1 0-4h.05A1.7 1.7 0 0 0 3.4 8a1.7 1.7 0 0 0-.34-1.88L3 6.06a2 2 0 0 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 8 3.6a1.7 1.7 0 0 0 1-.2A1.7 1.7 0 0 0 10 1.85V1.8a2 2 0 0 1 4 0v.05A1.7 1.7 0 0 0 15 3.4a1.7 1.7 0 0 0 1.9-.34l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 8c.08.34.08.68 0 1 .44.18.9.28 1.55.28H21a2 2 0 0 1 0 4h-.05A1.7 1.7 0 0 0 19.4 15Z"></path>
                                </svg>
                        @endswitch
                    </span>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="account-main min-w-0">
        <header class="account-topbar">
            <div>
                <p class="topbar-kicker">@yield('page-kicker', strtoupper($__env->yieldContent('page-title', 'Dashboard')))</p>
                <h1 class="topbar-title">Welcome back, {{ $currentUser?->name ?? 'Distributor' }}</h1>
                <p class="topbar-date">{{ now()->format('F j, Y') }}</p>
            </div>

            <div class="topbar-actions">
                @if($currentUser?->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="neon-outline-button">Admin</a>
                @endif

                <div x-data="{ open: false }" class="profile-menu">
                    <button type="button" class="profile-button" x-on:click.stop="open = ! open" x-on:keydown.escape.window="open = false" :aria-expanded="open.toString()">
                        <span class="profile-avatar">
                            @if($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="" class="profile-avatar-image">
                            @else
                                {{ strtoupper(substr($currentUser?->name ?? 'AF', 0, 1)) }}
                            @endif
                        </span>
                        <span class="min-w-0 text-left">
                            <span class="profile-name">{{ $currentUser?->name ?? 'AFRITEL Distributor' }}</span>
                            <span class="profile-role">Master Distributor</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-cyan-200/80" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition x-cloak x-on:click.outside="open = false" class="profile-dropdown">
                        <a href="{{ route('account.settings.edit') }}" class="profile-dropdown-link">Profile</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="profile-logout">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="account-content">
            @if (session('status'))
                <div class="dashboard-alert dashboard-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="dashboard-alert dashboard-alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@yield('scripts')
</body>
</html>
