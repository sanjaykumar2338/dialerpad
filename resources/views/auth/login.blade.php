<x-guest-layout>
    <div class="space-y-4 text-center">
        <h1 class="text-2xl font-semibold text-white">Welcome back</h1>
        <p class="text-sm text-slate-300">Sign in to manage call cards, sessions, and eSIM requests.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4 mt-6">
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
</x-guest-layout>
