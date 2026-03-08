@extends('admin.layouts.app')
@section('title', 'Vendor Dashboard')
@section('content')
<div class="container-fluid">
    <x-page-title title="Vendor Dashboard" :breadcrumbs="[['label' => 'Dashboard']]" />
    <x-alert />

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
                <div class="card-body">
                    <h5 class="card-title">Quick links</h5>
                    <a href="{{ route('admin.vendor.hotels.index') }}" class="btn btn-outline-primary btn-sm d-block mb-2">My Hotels</a>
                    <a href="{{ route('admin.vendor.rooms.index') }}" class="btn btn-outline-primary btn-sm d-block mb-2">Rooms & Availability</a>
                    <a href="{{ route('admin.vendor.reports.index') }}" class="btn btn-outline-primary btn-sm d-block mb-2">Reports</a>
                    <a href="{{ route('admin.vendor.bookings.index') }}" class="btn btn-outline-primary btn-sm d-block mb-2">Bookings</a>
                    <a href="{{ route('admin.vendor.payouts.index') }}" class="btn btn-outline-primary btn-sm d-block">Payout history</a>
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
                labels: @json($revenueChart['labels']),
                datasets: [{ label: 'Revenue ($)', data: @json($revenueChart['data']), backgroundColor: 'rgba(81, 86, 190, 0.6)', borderColor: 'rgba(81, 86, 190, 1)', borderWidth: 1 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }
});
</script>
@endsection
