@extends('admin.layouts.app')
@section('title', 'Booking Disputes')
@section('content')
<div class="container-fluid">
    <x-page-title title="Booking Disputes" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Disputes']]" />
    <x-alert />
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>In review</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr><th>ID</th><th>Booking</th><th>Hotel</th><th>Status</th><th>Created</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($disputes as $d)
                    <tr>
                        <td>{{ $d->id }}</td>
                        <td><a href="{{ route('admin.disputes.show', $d) }}">{{ $d->booking->uuid ?? $d->booking_id }}</a></td>
                        <td>{{ $d->booking->hotel->name ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $d->status }}</span></td>
                        <td>{{ $d->created_at->format('Y-m-d') }}</td>
                        <td><a href="{{ route('admin.disputes.show', $d) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-muted">No disputes.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $disputes->links() }}
        </div>
    </div>
</div>
@endsection
