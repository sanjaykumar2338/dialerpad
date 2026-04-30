<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Distributor Login - DialerPad</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            color-scheme: dark;
            --bg: radial-gradient(circle at 22% 20%, #0ea5e9, #0f172a 45%), radial-gradient(circle at 82% 12%, #22c55e, transparent 35%), #020617;
            --border: rgba(255, 255, 255, 0.1);
            --muted: #94a3b8;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }
        .auth-shell {
            width: 100%;
            max-width: 440px;
            background: linear-gradient(145deg, rgba(15,23,42,0.88), rgba(15,23,42,0.72));
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
        }
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
        }
        .logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fde68a, #f97316);
            display: grid;
            place-items: center;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .brand-title {
            font-size: 1.2rem;
            margin: 0;
        }
        .brand-sub {
            margin: 0;
            color: var(--muted);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="brand">
            <div class="logo">DP</div>
            <div>
                <p class="brand-title">DialerPad</p>
                <p class="brand-sub">Distributor portal</p>
            </div>
        </div>

        <div class="space-y-4 text-center">
            <h1 class="text-2xl font-semibold text-white">Distributor Login</h1>
            <p class="text-sm text-slate-300">Sign in to request stock, review batches, and track reports.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('distributor.login.store') }}" class="space-y-4 mt-6">
            @csrf

            <div class="space-y-1">
                <label for="email" class="block text-sm font-semibold text-slate-100">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full rounded-xl bg-slate-800 border border-slate-700 px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="space-y-1">
                <label for="password" class="block text-sm font-semibold text-slate-100">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="w-full rounded-xl bg-slate-800 border border-slate-700 px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="flex items-center justify-between text-sm text-slate-300">
                <label for="remember_me" class="inline-flex items-center gap-2">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-600 text-emerald-500 focus:ring-emerald-500" name="remember">
                    <span>Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-emerald-400 hover:text-emerald-300" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit"
                    class="w-full rounded-full bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold py-3 transition">
                Log in
            </button>
        </form>

        <div class="mt-5 text-center text-sm text-slate-400">
            <a class="text-emerald-400 hover:text-emerald-300" href="{{ route('login') }}">Admin Login</a>
        </div>
    </div>
</body>
</html>
