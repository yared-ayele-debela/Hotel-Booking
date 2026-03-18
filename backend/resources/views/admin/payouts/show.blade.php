@extends('admin.layouts.app')
@section('title', 'Payout #' . $payout->id)
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Payout #{{ $payout->id }}"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Payouts', 'url' => route('admin.payouts.index')],
            ['label' => '#' . $payout->id]
        ]"
    />
    <x-alert />

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th>Vendor</th><td><a href="{{ route('admin.vendors.show', $payout->vendor_id) }}">{{ $payout->vendor->name ?? $payout->vendor_id }}</a> ({{ $payout->vendor->email ?? '' }})</td></tr>
                        <tr><th>Period</th><td>{{ $payout->period_start->format('M d, Y') }} – {{ $payout->period_end->format('M d, Y') }}</td></tr>
                        <tr><th>Gross</th><td>${{ number_format($payout->amount, 2) }}</td></tr>
                        <tr><th>Commission</th><td>${{ number_format($payout->commission, 2) }}</td></tr>
                        <tr><th>Net</th><td><strong>${{ number_format($payout->net, 2) }}</strong></td></tr>
                        <tr><th>Status</th><td>
                            @if($payout->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($payout->status === 'processing')
                                <span class="badge bg-info">Processing</span>
                            @else
                                <span class="badge bg-success">Paid</span>
                            @endif
                        </td></tr>
                        @if($payout->reference)
                        <tr><th>Reference</th><td>{{ $payout->reference }}</td></tr>
                        @endif
                        @if($payout->paid_at)
                        <tr><th>Paid at</th><td>{{ $payout->paid_at->format('M d, Y H:i') }}</td></tr>
                        @endif
                    </table>
                    @if(!$payout->isPaid())
                    <form method="POST" action="{{ route('admin.payouts.mark-paid', $payout) }}" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="reference" class="form-control" placeholder="Reference (optional)">
                            <button type="submit" class="btn btn-success">Mark as paid</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Included bookings ({{ $payout->bookings->count() }})</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr><th>Booking</th><th>Hotel</th><th>Check-in</th><th>Check-out</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @foreach($payout->bookings as $b)
                    <tr>
                        <td><code>{{ $b->uuid ?? $b->id }}</code></td>
                        <td>{{ $b->hotel->name ?? '-' }}</td>
                        <td>{{ $b->check_in?->format('Y-m-d') ?? '-' }}</td>
                        <td>{{ $b->check_out?->format('Y-m-d') ?? '-' }}</td>
                        <td>${{ number_format($b->total_price ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
