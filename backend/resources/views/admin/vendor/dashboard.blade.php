@extends('admin.layouts.app')
@section('title', 'Vendor Dashboard')
@section('content')
<div class="container-fluid">
    <x-page-title title="Vendor Dashboard" :breadcrumbs="[['label' => 'Dashboard']]" />
    <x-alert />

    @php
        $vendorUser = auth()->user();
        $vendorUser->loadMissing('vendorProfile');
    @endphp
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    @include('admin.partials.user-avatar', ['user' => $vendorUser, 'size' => 56])
                    <div>
                        <h5 class="mb-0">Welcome back, {{ $vendorUser->name }}</h5>
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

    @if(isset($vendorApproved) && !$vendorApproved)
    <div class="alert alert-warning mb-4" role="alert">
        <strong>Pending approval.</strong> Your account is under review. You will be able to add hotels and receive bookings once approved by our team.
    </div>
    @endif

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Revenue (confirmed)</span>
                    <h4 class="mb-0">${{ number_format($revenue, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Total Bookings</span>
                    <h4 class="mb-0">{{ number_format($bookingCount) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Commission</span>
                    <h4 class="mb-0">${{ number_format($commissionAmount, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block">Net</span>
                    <h4 class="mb-0">${{ number_format($net, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenue (last 6 months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bookings by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="bookingsByStatusChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bookings Trend (last 6 months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="bookingsTrendChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick links</h5>
                    <a href="{{ route('admin.vendor.profile.edit') }}" class="btn btn-outline-primary btn-sm d-block mb-2">Business Details</a>
                    <a href="{{ route('admin.vendor.hotels.index') }}" class="btn btn-outline-primary btn-sm d-block mb-2">My Hotels</a>
                    <a href="{{ route('admin.vendor.rooms.index') }}" class="btn btn-outline-primary btn-sm d-block mb-2">Rooms & Availability</a>
                    <a href="{{ route('admin.vendor.reports.index') }}" class="btn btn-outline-primary btn-sm d-block mb-2">Reports</a>
                    <a href="{{ route('admin.vendor.bookings.index') }}" class="btn btn-outline-primary btn-sm d-block mb-2">Bookings</a>
                    <a href="{{ route('admin.vendor.payouts.index') }}" class="btn btn-outline-primary btn-sm d-block">Payout history</a>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($topHotelsChart['labels']))
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Hotels by Revenue</h5>
                </div>
                <div class="card-body">
                    <canvas id="topHotelsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: @json($revenueChart['labels']),
                datasets: [{ label: 'Revenue ($)', data: @json($revenueChart['data']), backgroundColor: 'rgba(81, 86, 190, 0.6)', borderColor: 'rgba(81, 86, 190, 1)', borderWidth: 1 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
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
                    backgroundColor: ['rgba(255, 193, 7, 0.8)', 'rgba(40, 199, 111, 0.8)', 'rgba(220, 53, 69, 0.8)', 'rgba(13, 110, 253, 0.8)'],
                    borderWidth: 1
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
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
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
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
                    backgroundColor: 'rgba(40, 199, 111, 0.6)',
                    borderColor: 'rgba(40, 199, 111, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });
    }
});
</script>
@endsection
