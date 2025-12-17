@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white px-4 py-12">
    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-8 items-center">
        <div class="lg:col-span-2 space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-sm font-semibold">
                eSIM Activation
            </div>
            <h1 class="text-3xl font-semibold leading-tight">You’re activating: <span class="text-emerald-400">{{ $type->name }}</span></h1>
            <p class="text-slate-300 text-sm leading-relaxed">
                This QR is tied to the plan above. Share your contact and device details and we’ll email you the live eSIM QR after activation.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="p-3 rounded-xl border border-slate-800 bg-white/5">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-1">Processing</div>
                    <div class="text-lg font-semibold">Instant email</div>
                    <p class="text-slate-400 text-sm">We activate and email the QR to you.</p>
                </div>
                <div class="p-3 rounded-xl border border-slate-800 bg-white/5">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-1">Status</div>
                    <div class="text-lg font-semibold">Pending → Processed</div>
                    <p class="text-slate-400 text-sm">Each QR can be used once.</p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl shadow-2xl p-6 backdrop-blur">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Form</div>
                        <h2 class="text-xl font-semibold">Submit eSIM request</h2>
                    </div>
                    <div class="text-xs text-slate-400">Plan is preselected</div>
                </div>

                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 text-emerald-100 px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-500/40 bg-rose-500/10 text-rose-100 px-4 py-3">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('esim.submit', $code->uuid) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="full_name" class="text-sm font-semibold text-slate-100">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required
                                   class="w-full rounded-xl bg-slate-800 border border-slate-700 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        <div class="space-y-1">
                            <label for="email" class="text-sm font-semibold text-slate-100">Email *</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                   class="w-full rounded-xl bg-slate-800 border border-slate-700 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="phone" class="text-sm font-semibold text-slate-100">Phone (optional)</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                   class="w-full rounded-xl bg-slate-800 border border-slate-700 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        <div class="space-y-1">
                            <label for="device_model" class="text-sm font-semibold text-slate-100">Device Model (optional)</label>
                            <input type="text" id="device_model" name="device_model" value="{{ old('device_model') }}"
                                   class="w-full rounded-xl bg-slate-800 border border-slate-700 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-100">Plan</label>
                        <div class="rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-3 text-slate-100 text-sm">
                            {{ $type->name }}
                            @if ($type->provider_reference_code)
                                <span class="text-slate-400">— {{ $type->provider_reference_code }}</span>
                            @endif
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full rounded-full bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold py-3 transition">
                        Submit Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
