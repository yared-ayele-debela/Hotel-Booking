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
                                    <th>Status</th>
                                    <th width="180">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendors->take(5) as $v)
                                <tr>
                                    <td>{{ $v->name }}</td>
                                    <td>{{ $v->email }}</td>
                                    <td>
                                        <span class="badge {{ $v->status === 'active' ? 'bg-success' : 'bg-warning' }}">{{ $v->status }}</span>
                                    </td>
                                    <td>
                                        @if($v->status === 'suspended')
                                        <form action="{{ route('admin.vendors.update-status', $v) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        @else
                                        <form action="{{ route('admin.vendors.update-status', $v) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Suspend this vendor?')">Suspend</button>
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

@if($isSuperAdmin && !empty($revenueChart['labels']))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
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
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});
</script>
@endif
@endsection
