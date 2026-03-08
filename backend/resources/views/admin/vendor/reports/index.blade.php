@extends('admin.layouts.app')
@section('title', 'Reports')
@section('content')
<div class="container-fluid">
    <x-page-title title="Occupancy & Revenue Reports" :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.vendor.dashboard')], ['label' => 'Reports']]" />
    <x-alert />

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted">Period:</span>
            <a href="{{ route('admin.vendor.reports.index', ['period' => 'day']) }}" class="btn btn-sm {{ ($period ?? '') === 'day' ? 'btn-primary' : 'btn-outline-secondary' }}">Last 7 days</a>
            <a href="{{ route('admin.vendor.reports.index', ['period' => 'week']) }}" class="btn btn-sm {{ ($period ?? '') === 'week' ? 'btn-primary' : 'btn-outline-secondary' }}">Last 4 weeks</a>
            <a href="{{ route('admin.vendor.reports.index', ['period' => 'month']) }}" class="btn btn-sm {{ ($period ?? 'month') === 'month' ? 'btn-primary' : 'btn-outline-secondary' }}">Last month</a>
        </div>
        <a href="{{ route('admin.vendor.reports.export', request()->only(['from', 'to'])) }}" class="btn btn-success">
            <i data-feather="download" class="me-1" style="width:14px;height:14px"></i> Export CSV
        </a>
    </div>

    {{-- Occupancy --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Occupancy ({{ $occupancy['period_label'] ?? 'Last month' }})</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h2 class="mb-0">{{ $occupancy['occupancy'] ?? 0 }}%</h2>
                            <p class="text-muted mb-0">Booked: {{ number_format($occupancy['booked_nights'] ?? 0) }} / Available: {{ number_format($occupancy['available_nights'] ?? 0) }} room-nights</p>
                        </div>
                        <div class="col-md-8">
                            <div class="progress" style="height: 24px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ min($occupancy['occupancy'] ?? 0, 100) }}%" aria-valuenow="{{ $occupancy['occupancy'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Period comparison --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenue comparison</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1">Current: ${{ number_format($comparison['current']['revenue'] ?? 0, 2) }}</p>
                    <p class="mb-1 text-muted">Previous: ${{ number_format($comparison['previous']['revenue'] ?? 0, 2) }}</p>
                    @php $revChg = $comparison['revenue_change_pct'] ?? 0; @endphp
                    <p class="mb-0">
                        <span class="badge {{ $revChg >= 0 ? 'bg-success' : 'bg-danger' }}">{{ $revChg >= 0 ? '+' : '' }}{{ $revChg }}%</span> vs previous period
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bookings comparison</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1">Current: {{ $comparison['current']['bookings'] ?? 0 }}</p>
                    <p class="mb-1 text-muted">Previous: {{ $comparison['previous']['bookings'] ?? 0 }}</p>
                    @php $bkgChg = $comparison['bookings_change_pct'] ?? 0; @endphp
                    <p class="mb-0">
                        <span class="badge {{ $bkgChg >= 0 ? 'bg-success' : 'bg-danger' }}">{{ $bkgChg >= 0 ? '+' : '' }}{{ $bkgChg }}%</span> vs previous period
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue by room type --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenue by room type</h5>
                </div>
                <div class="card-body">
                    @if(!empty($revenueByRoom))
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Revenue</th>
                                    <th>Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueByRoom as $r)
                                <tr>
                                    <td>{{ $r['room_name'] }}</td>
                                    <td>${{ number_format($r['revenue'], 2) }}</td>
                                    <td>{{ $r['bookings'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">No revenue data for the selected period.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue chart --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenue (last 6 months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($revenueChart['labels'] ?? []),
                datasets: [{ label: 'Revenue ($)', data: @json($revenueChart['data'] ?? []), backgroundColor: 'rgba(81, 86, 190, 0.6)', borderColor: 'rgba(81, 86, 190, 1)', borderWidth: 1 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }
});
</script>
@endsection
