@extends('layouts.public')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-950 text-white px-4">
    <div class="max-w-lg w-full text-center space-y-4 bg-slate-900/70 border border-slate-800 rounded-2xl p-8">
        <div class="text-rose-400 text-sm font-semibold uppercase tracking-[0.2em]">Link unavailable</div>
        <h1 class="text-2xl font-semibold">This eSIM QR has already been used</h1>
        <p class="text-slate-300 text-sm">If you believe this is a mistake, request a new QR code from the issuer.</p>
    </div>
</div>
@endsection
