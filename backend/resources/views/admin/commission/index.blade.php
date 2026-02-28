@extends('admin.layouts.app')
@section('title', 'Commission')
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Commission"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Commission']
        ]"
    />
    <x-alert />

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Current rate</h5>
                    <p class="display-6">{{ number_format($rate * 100, 1) }}%</p>
                    <a href="{{ route('admin.commission.edit') }}" class="btn btn-primary">Edit rate</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Commission by vendor</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Payout status and detailed payouts can be added here.</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vendor ID</th>
                                    <th>Bookings</th>
                                    <th>Gross</th>
                                    <th>Commission</th>
                                    <th>Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $row)
                                <tr>
                                    <td>{{ $row['vendor_id'] }}</td>
                                    <td>{{ $row['booking_count'] }}</td>
                                    <td>${{ number_format((float) $row['gross'], 2) }}</td>
                                    <td>${{ number_format((float) $row['commission'], 2) }}</td>
                                    <td>${{ number_format((float) $row['net'], 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-muted">No data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
