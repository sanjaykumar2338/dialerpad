@extends('account.layout')

@section('page-title', 'Reports')
@section('page-kicker', 'REPORTS')

@section('content')
    @php
        $formatNumber = fn ($value) => number_format((int) ($value ?? 0));
        $formatPercent = fn ($value) => rtrim(rtrim(number_format((float) ($value ?? 0), 1), '0'), '.').'%';
        $statusLabel = fn ($value) => $value ? str_replace('_', ' ', ucwords($value, '_')) : '-';
        $productLabel = fn ($value) => $value === \App\Models\BatchRequest::PRODUCT_ESIM ? 'eSIM' : 'Call card';
        $metrics = array_merge([
            'total' => 0,
            'activated' => 0,
            'remaining' => 0,
            'activation_rate' => 0,
            'expired' => 0,
        ], $metrics ?? []);
        $inventoryStatus = array_merge([
            'unused' => 0,
            'activated' => 0,
            'expired' => 0,
        ], $inventoryStatus ?? []);
    @endphp

    <div class="report-page">
        <section class="report-heading">
            <p class="report-kicker">REPORTS</p>
            <h2 class="report-title">Distribution performance overview</h2>
            <p class="report-subtitle">Distributor analytics across assigned call cards, eSIM codes, batch requests, and inventory status.</p>
        </section>

        <section class="report-summary-grid" aria-label="Report summary">
            <article class="account-panel account-stat-card">
                <p class="account-stat-label">Total Cards Received</p>
                <p class="account-stat-value">{{ $formatNumber($metrics['total']) }}</p>
            </article>
            <article class="account-panel account-stat-card">
                <p class="account-stat-label">Total Activated</p>
                <p class="account-stat-value">{{ $formatNumber($metrics['activated']) }}</p>
            </article>
            <article class="account-panel account-stat-card">
                <p class="account-stat-label">Total Remaining</p>
                <p class="account-stat-value">{{ $formatNumber($metrics['remaining']) }}</p>
            </article>
            <article class="account-panel account-stat-card">
                <p class="account-stat-label">Activation Rate</p>
                <p class="account-stat-value">{{ $formatPercent($metrics['activation_rate']) }}</p>
            </article>
            <article class="account-panel account-stat-card">
                <p class="account-stat-label">Expired Cards</p>
                <p class="account-stat-value">{{ $formatNumber($metrics['expired']) }}</p>
            </article>
        </section>

        <section class="report-two-column-grid">
            <article class="account-panel report-panel">
                <div class="report-panel-header">
                    <h2>Requests by Status</h2>
                    <p>Batch request lifecycle counts</p>
                </div>
                <div class="report-panel-body">
                    @forelse ($requestCounts ?? [] as $status => $count)
                        <div class="account-list-row">
                            <span class="status-badge status-{{ $status }}">{{ $statusLabel($status) }}</span>
                            <span class="account-list-value">{{ $formatNumber($count) }}</span>
                        </div>
                    @empty
                        <p class="empty-side-state">No request status data yet.</p>
                    @endforelse
                </div>
            </article>

            <article class="account-panel report-panel">
                <div class="report-panel-header">
                    <h2>Requests by Product</h2>
                    <p>Demand split by product type</p>
                </div>
                <div class="report-panel-body">
                    @forelse ($productCounts ?? [] as $product => $count)
                        <div class="account-list-row">
                            <span class="account-muted-text">{{ $productLabel($product) }}</span>
                            <span class="account-list-value">{{ $formatNumber($count) }}</span>
                        </div>
                    @empty
                        <p class="empty-side-state">No product request data yet.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="account-panel report-panel">
            <div class="report-panel-header">
                <h2>Batch Performance</h2>
                <p>Latest batch utilization by assigned inventory</p>
            </div>
            <div class="neon-table-wrap">
                <table class="neon-table report-table">
                    <thead>
                        <tr>
                            <th>Batch ID</th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Activation Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batchPerformance ?? [] as $batch)
                            <tr>
                                <td class="batch-id">{{ $batch['batch_id'] ?? '-' }}</td>
                                <td>{{ $batch['product_label'] ?? '-' }}</td>
                                <td>
                                    <span class="status-badge status-{{ $batch['status'] ?? 'pending' }}">{{ $statusLabel($batch['status'] ?? null) }}</span>
                                </td>
                                <td>{{ $formatNumber($batch['total_cards'] ?? 0) }}</td>
                                <td>{{ $formatNumber($batch['used_cards'] ?? 0) }}</td>
                                <td>{{ $formatNumber($batch['remaining_cards'] ?? 0) }}</td>
                                <td>{{ $formatPercent($batch['activation_rate'] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">No batch performance data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="account-panel report-panel">
            <div class="report-panel-header">
                <h2>Inventory Status</h2>
                <p>Assigned inventory grouped by current state</p>
            </div>
            <div class="inventory-status-grid">
                <article class="inventory-status-item">
                    <p>Unused</p>
                    <strong>{{ $formatNumber($inventoryStatus['unused']) }}</strong>
                </article>
                <article class="inventory-status-item">
                    <p>Activated</p>
                    <strong>{{ $formatNumber($inventoryStatus['activated']) }}</strong>
                </article>
                <article class="inventory-status-item">
                    <p>Expired</p>
                    <strong>{{ $formatNumber($inventoryStatus['expired']) }}</strong>
                </article>
            </div>
        </section>
    </div>
@endsection
