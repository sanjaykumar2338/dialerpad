@extends('admin.layout')

@section('page-title', 'Call Card Details')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="mb-0 text-white">{{ $card->name }}</h2>
            <small class="text-white-50">Card details & call history</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.call-sessions.index', ['card_id' => $card->id]) }}" class="btn btn-outline-light">View Sessions</a>
            <a href="{{ route('admin.call-cards.edit', $card) }}" class="btn btn-primary">Edit Card</a>
        </div>
    </div>

    @php
        $percent = $card->total_minutes > 0
            ? round(($card->used_minutes / $card->total_minutes) * 100)
            : 0;
        $percent = min(100, max(0, $percent));
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-transparent border-light h-100 text-white">
                <div class="card-body">
                    <div class="text-white-50 text-uppercase fw-semibold small mb-1">Prefix</div>
                    <div class="fs-5 fw-bold text-white">{{ $card->prefix }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light h-100 text-white">
                <div class="card-body">
                    <div class="text-white-50 text-uppercase fw-semibold small mb-1">Total Minutes</div>
                    <div class="fs-5 fw-bold text-white">{{ $card->total_minutes }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light h-100 text-white">
                <div class="card-body">
                    <div class="text-white-50 text-uppercase fw-semibold small mb-1">Used Minutes</div>
                    <div class="fs-5 fw-bold text-white">{{ $card->used_minutes }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light h-100 text-white">
                <div class="card-body">
                    <div class="text-white-50 text-uppercase fw-semibold small mb-1">Remaining</div>
                    <div class="fs-5 fw-bold text-white">{{ $card->remaining_minutes }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-transparent border-light mb-4 text-white">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold text-white">Usage</div>
                <small class="text-white-50">{{ $percent }}%</small>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <div class="card bg-transparent border-light text-white">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 text-white">Call Sessions</h4>
                <a href="{{ route('admin.call-sessions.index', ['card_id' => $card->id]) }}" class="text-decoration-none text-success small">Open Sessions List</a>
            </div>
            @if($card->sessions->isEmpty())
                <p class="text-white-50 mb-0">No calls yet for this card.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Dialed / Full Number</th>
                                <th>Duration (s)</th>
                                <th>Remaining After</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($card->sessions as $session)
                                <tr>
                                    <td>{{ $session->full_number ?? $session->dialed_number }}</td>
                                    <td>{{ $session->duration_seconds ?? '-' }}</td>
                                    <td>{{ $session->remaining_minutes_after_call ?? $card->remaining_minutes ?? '—' }}</td>
                                    <td>
                                        <span class="badge
                                            @if($session->status === 'completed') bg-success
                                            @elseif($session->status === 'failed') bg-danger
                                            @elseif($session->status === 'cancelled') bg-warning text-dark
                                            @else bg-info text-dark
                                            @endif">
                                            {{ ucfirst($session->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $session->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
