@extends('admin.layouts.app')
@section('title', 'Payout & Commission')
@section('content')
<div class="container-fluid">
    <x-page-title title="Payout & Commission" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Payouts']]" />
    <x-alert />
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Summary (confirmed bookings)</h5>
            <table class="table table-bordered w-auto">
                <tr><th>Bookings</th><td>{{ number_format($totals['booking_count']) }}</td></tr>
                <tr><th>Gross revenue</th><td>${{ number_format($totals['gross'], 2) }}</td></tr>
                <tr><th>Commission</th><td>${{ number_format($totals['commission'], 2) }}</td></tr>
                <tr><th>Net</th><td>${{ number_format($totals['net'], 2) }}</td></tr>
            </table>
            <p class="text-muted small mb-0">Full payout history and payouts will be added in a future update.</p>
        </div>
    </div>
</div>
@endsection
