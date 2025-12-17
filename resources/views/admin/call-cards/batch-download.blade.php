@extends('admin.layout')

@section('page-title', 'Download QR Batch')

@section('content')
    <div class="card bg-transparent border border-secondary">
        <div class="card-body">
            <h5 class="card-title">Your QR pack is downloading</h5>
            <p class="mb-3 text-muted">
                We’re preparing the ZIP for batch <code>{{ $batch }}</code>. The download should start automatically.
            </p>
            <div class="d-flex gap-2">
                <a href="{{ $downloadUrl }}" class="btn btn-primary">Download now</a>
                <a href="{{ $redirectUrl }}" class="btn btn-outline-light">Back to Call Cards</a>
            </div>
            <p class="mt-3 text-muted small mb-0">
                You’ll be sent back to the Call Cards page shortly.
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Trigger the ZIP download without leaving this page.
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = @json($downloadUrl);
            document.body.appendChild(iframe);

            // Redirect back after a short delay to avoid the create page hanging.
            setTimeout(() => {
                window.location.href = @json($redirectUrl);
            }, 3000);
        });
    </script>
@endsection
