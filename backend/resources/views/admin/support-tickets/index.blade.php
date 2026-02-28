@extends('admin.layouts.app')
@section('title', 'Support Tickets')
@section('content')
<div class="container-fluid">
    <x-page-title title="Support Tickets" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Support Tickets']]" />
    <x-alert />
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
                    <tr><th>ID</th><th>Subject</th><th>User</th><th>Status</th><th>Priority</th><th>Assigned to</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr>
                        <td>{{ $t->id }}</td>
                        <td>{{ Str::limit($t->subject, 40) }}</td>
                        <td>{{ $t->user->name ?? $t->user->email ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $t->status }}</span></td>
                        <td>{{ $t->priority }}</td>
                        <td>{{ $t->assignedTo->name ?? '-' }}</td>
                        <td><a href="{{ route('admin.support-tickets.show', $t) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-muted">No tickets.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection
