@extends('layouts.public')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-900 text-white">
    <div class="max-w-md text-center">
        <h1 class="text-2xl font-semibold mb-2">Card Expired</h1>
        <p class="text-slate-400 mb-4">
            This call card has no remaining minutes. Please contact support to get a new QR code.
        </p>
    </div>
</div>
@endsection
