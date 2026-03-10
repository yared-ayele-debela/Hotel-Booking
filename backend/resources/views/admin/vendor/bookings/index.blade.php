@extends('admin.layouts.app')
@section('title', 'Bookings')
@section('content')
<div class="container-fluid">
    <x-page-title title="Bookings" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Bookings']]" />
    <x-alert />
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">Hotel</label>
                    <select name="hotel_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ request('hotel_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr><th>Booking</th><th>Hotel</th><th>Customer</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Total</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                    <tr>
                        <td><code>{{ $b->uuid ?? $b->id }}</code></td>
                        <td>{{ $b->hotel->name ?? '-' }}</td>
                        <td>{{ $b->customer_id ? ($b->customer->name ?? $b->customer->email ?? '-') : ($b->guest_name ?? $b->guest_email ?? '-') }}</td>
                        <td>{{ $b->check_in ? $b->check_in->format('Y-m-d') : '-' }}</td>
                        <td>{{ $b->check_out ? $b->check_out->format('Y-m-d') : '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $b->status }}</span></td>
                        <td>${{ number_format($b->total_price ?? 0, 2) }}</td>
                        <td>
                            <a href="{{ route('admin.vendor.bookings.invoice', $b->uuid) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View invoice">Invoice</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-muted">No bookings match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
