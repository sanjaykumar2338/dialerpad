@php
    $qrContent = $qrContent ?? null;
@endphp

<p>Hi {{ $name }},</p>

<p>Your eSIM is ready. The QR is attached to this email. You can also use the details below if your device supports manual entry.</p>

@if ($qrContent)
    <p><strong>QR data:</strong> {{ \Illuminate\Support\Str::limit($qrContent, 120) }}</p>
@endif

@if (!empty($providerResponse))
    <p><strong>Provider response:</strong></p>
    <pre style="background:#f5f5f5; padding:12px; border-radius:8px; font-size:12px;">{{ json_encode($providerResponse, JSON_PRETTY_PRINT) }}</pre>
@endif

<p>If you didn’t request this eSIM, please ignore this email.</p>

<p>— The DialerPad team</p>
