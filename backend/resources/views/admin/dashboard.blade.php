@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="container-fluid">
    <x-page-title title="Dashboard" :breadcrumbs="[['label' => 'Dashboard']]" />
    <x-alert />

    {{-- Platform KPIs --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block text-truncate">Total Hotels</span>
                    <h4 class="mb-0">{{ number_format($kpis['total_hotels']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block text-truncate">Total Bookings</span>
                    <h4 class="mb-0">{{ number_format($kpis['total_bookings']) }}</h4>
                </div>
            </div>
        </div>
        @if($isSuperAdmin)
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block text-truncate">Revenue (confirmed)</span>
                    <h4 class="mb-0">${{ number_format($kpis['revenue'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <span class="text-muted mb-1 d-block text-truncate">Platform Commission</span>
                    <h4 class="mb-0">${{ number_format($kpis['commission'], 2) }}</h4>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($isSuperAdmin && !empty($revenueChart['labels']))
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Commission</h5>
                    <a href="{{ route('admin.commission.edit') }}" class="btn btn-sm btn-soft-primary">Edit rate</a>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-1">Current rate</p>
                    <h4 class="mb-0">{{ number_format($commissionRate * 100, 1) }}%</h4>
                    <a href="{{ route('admin.commission.index') }}" class="btn btn-sm btn-outline-primary mt-2">View report</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bookings by status & trend (all admins) --}}
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bookings by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="bookingsByStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bookings Trend (last 6 months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="bookingsTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if($isSuperAdmin && !empty($topVendorsChart['labels']))
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Vendors by Revenue</h5>
                </div>
                <div class="card-body">
                    <canvas id="topVendorsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isSuperAdmin && $vendors->isNotEmpty())
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Vendors</h5>
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-primary">View all</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Business</th>
                                    <th>Approval</th>
                                    <th>Account</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendors->take(5) as $v)
                                @php $profile = $v->vendorProfile; $approval = $profile?->status ?? 'pending'; @endphp
                                <tr>
                                    <td><a href="{{ route('admin.vendors.show', $v) }}">{{ $v->name }}</a></td>
                                    <td>{{ $v->email }}</td>
                                    <td>
                                        {{ $profile?->business_name ?? '—' }}
                                        @if($profile?->business_phone)
                                            <br><small class="text-muted">{{ $profile->business_phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($approval === 'approved')<span class="badge bg-success">Approved</span>
                                        @elseif($approval === 'rejected')<span class="badge bg-danger">Rejected</span>
                                        @else<span class="badge bg-warning">Pending</span>@endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $v->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $v->status }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.vendors.show', $v) }}" class="btn btn-sm btn-outline-primary me-1">View</a>
                                        @if($approval === 'pending')
                                        <form action="{{ route('admin.vendors.approve', $v) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        @elseif($approval === 'approved' && $v->status === 'active')
                                        <form action="{{ route('admin.vendors.update-status', $v) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Suspend?')">Suspend</button>
                                        </form>
                                        @elseif($v->status === 'suspended')
                                        <form action="{{ route('admin.vendors.update-status', $v) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Admin users</h5>
                    <p class="text-muted mb-0">Manage admin users and roles.</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary mt-2">Users</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($isSuperAdmin && !empty($revenueChart['labels']))
    var revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: @json($revenueChart['labels']),
                datasets: [{
                    label: 'Revenue ($)',
                    data: @json($revenueChart['data']),
                    backgroundColor: 'rgba(81, 86, 190, 0.6)',
                    borderColor: 'rgba(81, 86, 190, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
    @endif

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
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
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
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    var topVendorsCtx = document.getElementById('topVendorsChart');
    if (topVendorsCtx) {
        new Chart(topVendorsCtx, {
            type: 'bar',
            data: {
                labels: @json($topVendorsChart['labels'] ?? []),
                datasets: [{
                    label: 'Revenue ($)',
                    data: @json($topVendorsChart['data'] ?? []),
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
