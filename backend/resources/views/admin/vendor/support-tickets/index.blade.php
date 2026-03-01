@extends('admin.layouts.app')
@section('title', 'Support Tickets')
@section('content')
<div class="container-fluid">
    <x-page-title title="Support Tickets" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Support']]" />
    <x-alert />
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <p class="mb-0">Create a ticket for billing, booking, or technical help.</p>
            <a href="{{ route('admin.vendor.support-tickets.create') }}" class="btn btn-primary">New ticket</a>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In progress</option>
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
                    <tr><th>ID</th><th>Subject</th><th>Category</th><th>Status</th><th>Replies</th><th>Created</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr>
                        <td>{{ $t->id }}</td>
                        <td>{{ Str::limit($t->subject, 50) }}</td>
                        <td>{{ \App\Enums\TicketCategory::tryFrom($t->category)?->label() ?? $t->category }}</td>
                        <td><span class="badge bg-secondary">{{ $t->status }}</span></td>
                        <td>{{ $t->replies_count }}</td>
                        <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('admin.vendor.support-tickets.show', $t) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-muted">No tickets. <a href="{{ route('admin.vendor.support-tickets.create') }}">Create one</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection
