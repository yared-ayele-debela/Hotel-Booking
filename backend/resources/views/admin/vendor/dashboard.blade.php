@extends('admin.layouts.app')
@section('title', 'Vendor Dashboard')
@section('content')
@include('admin.partials.dashboard-styles')
<div class="container-fluid">
    <x-page-title title="Vendor Dashboard" :breadcrumbs="[['label' => 'Dashboard']]" />
    <x-alert />

    @php
        $vendorUser = auth()->user();
        $vendorUser->loadMissing('vendorProfile');
    @endphp

    <div class="row g-4 mb-2">
        <div class="col-12">
            <div class="card dash-hero">
                <div class="card-body py-4 px-4">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        @include('admin.partials.user-avatar', ['user' => $vendorUser, 'size' => 56])
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Welcome back, {{ $vendorUser->name }}</h5>
                            @if($vendorUser->vendorProfile?->business_name)
                                <p class="text-muted mb-0 small">{{ $vendorUser->vendorProfile->business_name }}</p>
                            @else
                                <p class="text-muted mb-0 small">Vendor account</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($vendorApproved) && !$vendorApproved)
    <div class="alert alert-warning border-0 rounded-3 shadow-sm mb-4" role="alert">
        <strong>Pending approval.</strong> Your account is under review. You will be able to add hotels and receive bookings once approved by our team.
    </div>
    @endif

    <div class="row g-4 mb-2">
        <div class="col-xl-3 col-md-6">
            <div class="card dash-stat-card">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="dash-stat-label">Revenue (confirmed)</div>
                        <div class="dash-stat-value">${{ number_format($revenue, 2) }}</div>
                    </div>
                    <div class="dash-stat-icon bg-success bg-opacity-10 text-success">
                        <i class="mdi mdi-currency-usd"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card dash-stat-card">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="dash-stat-label">Total Bookings</div>
                        <div class="dash-stat-value">{{ number_format($bookingCount) }}</div>
                    </div>
                    <div class="dash-stat-icon bg-info bg-opacity-10 text-info">
                        <i class="mdi mdi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card dash-stat-card">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="dash-stat-label">Commission</div>
                        <div class="dash-stat-value">${{ number_format($commissionAmount, 2) }}</div>
                    </div>
                    <div class="dash-stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="mdi mdi-percent-outline"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card dash-stat-card">
                <div class="card-body d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <div class="dash-stat-label">Net</div>
                        <div class="dash-stat-value">${{ number_format($net, 2) }}</div>
                    </div>
                    <div class="dash-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="mdi mdi-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-2">
        <div class="col-xl-8">
            <div class="card dash-chart-card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenue overview</h5>
                    <p class="card-subtitle mb-0">Confirmed revenue by month (last 6 months)</p>
                </div>
                <div class="card-body pt-2">
                    <div style="height: 280px; position: relative;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card dash-chart-card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bookings by status</h5>
                    <p class="card-subtitle mb-0">Current pipeline snapshot</p>
                </div>
                <div class="card-body">
                    <div style="height: 260px; position: relative;">
                        <canvas id="bookingsByStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-2">
        <div class="col-xl-8">
            <div class="card dash-chart-card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Booking volume</h5>
                    <p class="card-subtitle mb-0">Monthly trend (last 6 months)</p>
                </div>
                <div class="card-body">
                    <div style="height: 260px; position: relative;">
                        <canvas id="bookingsTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card dash-chart-card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick links</h5>
                    <p class="card-subtitle mb-0">Manage your business</p>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.vendor.profile.edit') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="mdi mdi-domain me-2"></i>Business details
                    </a>
                    <a href="{{ route('admin.vendor.hotels.index') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="mdi mdi-office-building me-2"></i>My hotels
                    </a>
                    <a href="{{ route('admin.vendor.rooms.index') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="mdi mdi-bed-outline me-2"></i>Rooms & availability
                    </a>
                    <a href="{{ route('admin.vendor.reports.index') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="mdi mdi-chart-box-outline me-2"></i>Reports
                    </a>
                    <a href="{{ route('admin.vendor.bookings.index') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="mdi mdi-calendar-multiple me-2"></i>Bookings
                    </a>
                    <a href="{{ route('admin.vendor.payouts.index') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="mdi mdi-bank-transfer me-2"></i>Payout history
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($topHotelsChart['labels']))
    <div class="row g-4">
        <div class="col-xl-8 col-lg-12">
            <div class="card dash-chart-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top hotels</h5>
                    <p class="card-subtitle mb-0">By confirmed revenue (top properties)</p>
                </div>
                <div class="card-body">
                    <div style="height: 280px; position: relative;">
                        <canvas id="topHotelsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const grid = 'rgba(148, 163, 184, 0.22)';
    const tick = '#64748b';
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Segoe UI', system-ui, -apple-system, sans-serif";
        Chart.defaults.color = tick;
        Chart.defaults.scale.grid.color = grid;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.92)';
        Chart.defaults.plugins.tooltip.titleFont = { weight: '600', size: 13 };
        Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.padding = 16;
    }

    const colors = {
        indigo: { fill: 'rgba(79, 70, 229, 0.85)', stroke: 'rgb(67, 56, 202)', fillSoft: 'rgba(79, 70, 229, 0.12)' },
        emerald: { fill: 'rgba(5, 150, 105, 0.85)', stroke: 'rgb(4, 120, 87)' },
        status: ['rgba(245, 158, 11, 0.9)', 'rgba(16, 185, 129, 0.9)', 'rgba(244, 63, 94, 0.9)', 'rgba(59, 130, 246, 0.9)'],
    };

    document.addEventListener('DOMContentLoaded', function() {
    var revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: @json($revenueChart['labels']),
                datasets: [{
                    label: 'Revenue ($)',
                    data: @json($revenueChart['data']),
                    backgroundColor: colors.indigo.fill,
                    borderColor: colors.indigo.stroke,
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 48,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var v = ctx.parsed.y;
                                return ' $' + (v != null ? Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0');
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 0 } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) { return '$' + Number(v).toLocaleString(); }
                        }
                    }
                }
            }
        });
    }

    var statusCtx = document.getElementById('bookingsByStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: @json($bookingsByStatus['labels']),
                datasets: [{
                    data: @json($bookingsByStatus['data']),
                    backgroundColor: colors.status,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, font: { size: 12 } }
                    }
                }
            }
        });
    }

    var trendCtx = document.getElementById('bookingsTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($bookingsTrendChart['labels']),
                datasets: [{
                    label: 'Bookings',
                    data: @json($bookingsTrendChart['data']),
                    borderColor: colors.indigo.stroke,
                    backgroundColor: colors.indigo.fillSoft,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: colors.indigo.stroke,
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true }
                }
            }
        });
    }

    var topHotelsCtx = document.getElementById('topHotelsChart');
    if (topHotelsCtx) {
        new Chart(topHotelsCtx, {
            type: 'bar',
            data: {
                labels: @json($topHotelsChart['labels'] ?? []),
                datasets: [{
                    label: 'Revenue ($)',
                    data: @json($topHotelsChart['data'] ?? []),
                    backgroundColor: colors.emerald.fill,
                    borderColor: colors.emerald.stroke,
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 28,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var v = ctx.parsed.x;
                                return ' $' + (v != null ? Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) { return '$' + Number(v).toLocaleString(); }
                        }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }
    });
})();
</script>
@endsection
