@extends('account.layout')

@section('page-title', 'DASHBOARD')

@section('content')
    @php
        $formatNumber = fn ($value) => number_format((int) ($value ?? 0));
        $formatPercent = fn ($value) => rtrim(rtrim(number_format((float) ($value ?? 0), 1), '0'), '.').'%';
        $statusLabel = fn ($value) => $value ? str_replace('_', ' ', ucwords($value, '_')) : '-';
        $metrics = array_merge([
            'total' => 0,
            'activated' => 0,
            'remaining' => 0,
            'activation_rate' => 0,
        ], $metrics ?? []);

        $metricCards = [
            [
                'key' => 'total',
                'tone' => 'cyan',
                'label' => 'Total Cards Received',
                'value' => $formatNumber($metrics['total']),
                'trend' => 'Assigned inventory',
            ],
            [
                'key' => 'activated',
                'tone' => 'green',
                'label' => 'Cards Activated',
                'value' => $formatNumber($metrics['activated']),
                'trend' => $formatPercent($metrics['activation_rate']).' utilization',
            ],
            [
                'key' => 'remaining',
                'tone' => 'purple',
                'label' => 'Cards Remaining',
                'value' => $formatNumber($metrics['remaining']),
                'trend' => 'Ready for activation',
            ],
            [
                'key' => 'rate',
                'tone' => 'violet',
                'label' => 'Activation Rate',
                'value' => $formatPercent($metrics['activation_rate']),
                'trend' => 'Across received cards',
                'raw' => max(0, min(100, (float) $metrics['activation_rate'])),
            ],
        ];
    @endphp

    <div class="dashboard-page">
        <div class="dashboard-map" aria-hidden="true">
            <span class="map-arc map-arc-a"></span>
            <span class="map-arc map-arc-b"></span>
            <span class="map-arc map-arc-c"></span>
            <span class="map-node map-node-a"></span>
            <span class="map-node map-node-b"></span>
            <span class="map-node map-node-c"></span>
        </div>

        <div class="dashboard-stack">
            <section class="metrics-grid" aria-label="Dashboard metrics">
                @foreach ($metricCards as $card)
                    <article class="metric-card metric-card-{{ $card['tone'] }}">
                        <div class="min-w-0">
                            <p class="metric-label">{{ $card['label'] }}</p>
                            <p class="metric-value">{{ $card['value'] }}</p>
                            <p class="metric-trend trend-neutral">{{ $card['trend'] }}</p>
                        </div>

                        @if ($card['key'] === 'rate')
                            <div class="activation-ring" style="--value: {{ $card['raw'] }}" aria-hidden="true">
                                <span>{{ $card['value'] }}</span>
                            </div>
                        @else
                            <div class="metric-icon" aria-hidden="true">
                                @switch($card['key'])
                                    @case('total')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <rect x="3.5" y="5" width="17" height="13.5" rx="2.5"></rect>
                                            <path d="M3.5 9h17M7.5 14h3.5M15 14h2"></path>
                                        </svg>
                                        @break
                                    @case('activated')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <path d="M12 18.5a6.5 6.5 0 1 0 0-13"></path>
                                            <path d="m8 12 2.5 2.5L16.5 8"></path>
                                            <path d="M4 12h2M18 12h2M12 20v2M12 2v2"></path>
                                        </svg>
                                        @break
                                    @default
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <rect x="5" y="3.5" width="14" height="17" rx="2.5"></rect>
                                            <path d="M9 8h6M9 12h6M9 16h3"></path>
                                        </svg>
                                @endswitch
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>

            <section class="dashboard-grid">
                <div class="glass-panel batch-panel">
                    <div class="panel-header">
                        <div>
                            <h2>Batch Overview</h2>
                            <p>Assigned inventory performance</p>
                        </div>
                        <a href="{{ route('account.batches.index') }}" class="panel-link">View All Batches</a>
                    </div>

                    <div class="neon-table-wrap">
                        <table class="neon-table">
                            <thead>
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Status</th>
                                    <th>Total Cards</th>
                                    <th>Used Cards</th>
                                    <th>Remaining</th>
                                    <th>Activation Rate</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($batches ?? [] as $batch)
                                    @php
                                        $activationRate = max(0, min(100, (float) ($batch['activation_rate'] ?? 0)));
                                    @endphp
                                    <tr>
                                        <td class="batch-id">{{ $batch['batch_id'] ?? '-' }}</td>
                                        <td>
                                            <span class="status-badge status-{{ $batch['status'] ?? 'pending' }}">{{ $statusLabel($batch['status'] ?? null) }}</span>
                                        </td>
                                        <td>{{ $formatNumber($batch['total_cards'] ?? 0) }}</td>
                                        <td>{{ $formatNumber($batch['used_cards'] ?? 0) }}</td>
                                        <td>{{ $formatNumber($batch['remaining_cards'] ?? 0) }}</td>
                                        <td>{{ $formatPercent($activationRate) }}</td>
                                        <td class="progress-cell">
                                            <div class="table-progress" title="{{ $formatPercent($activationRate) }}">
                                                <span style="width: {{ $activationRate }}%"></span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">No batches assigned yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <aside class="side-panels">
                    <section class="glass-panel side-panel">
                        <div class="panel-header compact">
                            <h2>Recent Activity</h2>
                        </div>
                        <div class="activity-timeline">
                            @forelse ($activity ?? [] as $item)
                                @php
                                    $activityTone = str_contains($item['type'] ?? '', 'activated') || str_contains($item['type'] ?? '', 'completed')
                                        ? 'green'
                                        : (str_contains($item['type'] ?? '', 'request') ? 'purple' : 'cyan');
                                    $occurredAt = ! empty($item['occurred_at'])
                                        ? \Carbon\Carbon::parse($item['occurred_at'])->diffForHumans()
                                        : '-';
                                @endphp
                                <div class="activity-item">
                                    <span class="activity-dot activity-dot-{{ $activityTone }}" aria-hidden="true"></span>
                                    <div>
                                        <p class="activity-title">{{ $item['title'] ?? 'Activity' }}</p>
                                        <p class="activity-description">{{ $item['description'] ?? 'Distributor activity was recorded.' }}</p>
                                        <p class="activity-time">{{ $occurredAt }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="empty-side-state">No recent activity yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="glass-panel side-panel">
                        <div class="panel-header compact">
                            <h2>Request Status</h2>
                        </div>
                        <div class="request-list">
                            @forelse ($requests ?? [] as $requestItem)
                                <div class="request-row">
                                    <div class="min-w-0">
                                        <p class="request-title">Request #{{ $requestItem['id'] }}</p>
                                        <p class="request-meta">{{ $requestItem['product_label'] }} x {{ $formatNumber($requestItem['quantity']) }}</p>
                                    </div>
                                    <span class="status-badge status-{{ $requestItem['status'] }}">{{ $statusLabel($requestItem['status']) }}</span>
                                </div>
                            @empty
                                <p class="empty-side-state">No requests yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="glass-panel side-panel quick-actions">
                        <div class="panel-header compact">
                            <h2>Quick Actions</h2>
                        </div>
                        <div class="quick-action-list">
                            <a href="{{ route('account.requests.create') }}" class="neon-primary-button">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M10 4v12M4 10h12"></path>
                                </svg>
                                <span>Request New Batch</span>
                            </a>
                            <a href="{{ route('account.batches.index') }}" class="neon-secondary-button">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M4 5h12M4 10h12M4 15h12"></path>
                                </svg>
                                <span>View All Batches</span>
                            </a>
                        </div>
                    </section>
                </aside>
            </section>
        </div>
    </div>
@endsection
