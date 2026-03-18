@extends('admin.layouts.app')
@section('title', 'Payout & Commission')
@section('content')
<div class="container-fluid">
    <x-page-title title="Payout & Commission" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Payouts']]" />
    <x-alert />
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Summary (confirmed bookings, unpaid)</h5>
            <table class="table table-bordered w-auto">
                <tr><th>Bookings</th><td>{{ number_format($totals['booking_count']) }}</td></tr>
                <tr><th>Gross revenue</th><td>${{ number_format($totals['gross'], 2) }}</td></tr>
                <tr><th>Commission</th><td>${{ number_format($totals['commission'], 2) }}</td></tr>
                <tr><th>Net</th><td>${{ number_format($totals['net'], 2) }}</td></tr>
            </table>
            <p class="text-muted small mb-0">Totals include all confirmed bookings. Payouts are processed by the platform admin.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Payout history</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr><th>Period</th><th>Gross</th><th>Commission</th><th>Net</th><th>Status</th><th>Paid at</th></tr>
                </thead>
                <tbody>
                    @forelse($payouts as $p)
                    <tr>
                        <td>{{ $p->period_start->format('M d, Y') }} – {{ $p->period_end->format('M d, Y') }}</td>
                        <td>${{ number_format($p->amount, 2) }}</td>
                        <td>${{ number_format($p->commission, 2) }}</td>
                        <td><strong>${{ number_format($p->net, 2) }}</strong></td>
                        <td>
                            @if($p->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($p->status === 'processing')
                                <span class="badge bg-info">Processing</span>
                            @else
                                <span class="badge bg-success">Paid</span>
                            @endif
                        </td>
                        <td>{{ $p->paid_at?->format('M d, Y') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-muted">No payouts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $payouts->links() }}
        </div>
    </div>
</div>
@endsection
