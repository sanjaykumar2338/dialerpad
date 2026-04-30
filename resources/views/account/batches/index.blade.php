@extends('account.layout')

@section('page-title', 'My Batches')

@section('content')
    <div class="glass-panel batch-panel">
        <div class="panel-header">
            <div>
                <h2>Batch Overview</h2>
                <p>Assigned inventory and activation performance</p>
            </div>
            <div class="panel-shortcut-action">
                <p>Need more stock? Submit a new request first.</p>
                <a href="{{ route('account.requests.create', ['open' => 'create']) }}" class="neon-secondary-button">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M10 4v12M4 10h12"></path>
                    </svg>
                    <span>Submit Batch Request</span>
                </a>
            </div>
        </div>
        <div class="neon-table-wrap">
            <table class="neon-table">
                <thead>
                    <tr>
                        <th>Batch ID</th>
                        @if(auth()->user()?->is_admin)
                            <th>Account</th>
                        @endif
                        <th>Product</th>
                        <th>Status</th>
                        <th>Total Cards</th>
                        <th>Used Cards</th>
                        <th>Remaining Cards</th>
                        <th>Activation Rate</th>
                        <th>Created Date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td class="batch-id">{{ $batch['batch_id'] }}</td>
                            @if(auth()->user()?->is_admin)
                                <td>{{ $batch['account_name'] ?? '-' }}</td>
                            @endif
                            <td>{{ $batch['product_label'] }}</td>
                            <td>
                                <span class="status-badge status-{{ $batch['status'] }}">{{ $batch['status'] }}</span>
                            </td>
                            <td>{{ number_format($batch['total_cards']) }}</td>
                            <td>{{ number_format($batch['used_cards']) }}</td>
                            <td>{{ number_format($batch['remaining_cards']) }}</td>
                            <td class="activation-progress-cell">
                                <div class="activation-progress-label">
                                    <span>{{ rtrim(rtrim(number_format($batch['activation_rate'], 1), '0'), '.') }}%</span>
                                </div>
                                <div class="table-progress activation-progress">
                                    <span style="width: {{ min(100, max(0, $batch['activation_rate'])) }}%"></span>
                                </div>
                            </td>
                            <td>
                                {{ $batch['created_at'] ? \Carbon\Carbon::parse($batch['created_at'])->format('Y-m-d') : '-' }}
                            </td>
                            <td>
                                @if($batch['delivery_document_url'])
                                    <a href="{{ $batch['delivery_document_url'] }}" target="_blank" class="panel-link">Document</a>
                                @else
                                    <span class="account-muted-text">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->is_admin ? 10 : 9 }}" class="empty-state">
                                <div class="request-empty-state">
                                    <p>No batches assigned yet.</p>
                                    <span class="account-muted-text">Need more stock? Submit a new request first.</span>
                                    <a href="{{ route('account.requests.create', ['open' => 'create']) }}" class="neon-secondary-button">
                                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M10 4v12M4 10h12"></path>
                                        </svg>
                                        <span>Submit Batch Request</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($batches->hasPages())
            <div class="account-pagination">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
@endsection
