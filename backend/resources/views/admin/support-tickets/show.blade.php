@extends('admin.layouts.app')
@section('title', 'Ticket #' . $supportTicket->id)
@section('content')
<div class="container-fluid">
    <x-page-title title="Ticket #{{ $supportTicket->id }}" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Support Tickets', 'url' => route('admin.support-tickets.index')], ['label' => 'View']]" />
    <x-alert />
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Ticket</h5></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Subject:</strong> {{ $supportTicket->subject }}</p>
                    <p class="mb-1"><strong>Category:</strong> {{ \App\Enums\TicketCategory::tryFrom($supportTicket->category)?->label() ?? $supportTicket->category }}</p>
                    <p class="mb-1"><strong>From:</strong> {{ $supportTicket->user->name ?? '-' }} ({{ $supportTicket->user->email ?? '-' }})</p>
                    <p class="mb-1"><strong>Status:</strong> {{ $supportTicket->status }} | <strong>Priority:</strong> {{ $supportTicket->priority }}</p>
                    <p class="mb-0"><strong>Body:</strong></p>
                    <div class="border rounded p-2 mt-1">{{ $supportTicket->body }}</div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Replies</h5></div>
                <div class="card-body">
                    @forelse($supportTicket->replies as $reply)
                    <div class="border rounded p-2 mb-2">
                        <small class="text-muted">{{ $reply->user->name ?? $reply->user->email }} — {{ $reply->created_at->format('Y-m-d H:i') }}</small>
                        <div class="mt-1">{{ $reply->body }}</div>
                    </div>
                    @empty
                    <p class="text-muted mb-0">No replies yet.</p>
                    @endforelse
                    <hr>
                    <form action="{{ route('admin.support-tickets.replies.store', $supportTicket) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Add reply</label>
                            <textarea name="body" class="form-control" rows="3" required maxlength="10000">{{ old('body') }}</textarea>
                            @error('body')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Send reply</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Update ticket</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.support-tickets.update', $supportTicket) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['open','assigned','in_progress','resolved','closed'] as $s)
                                <option value="{{ $s }}" {{ $supportTicket->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigned to</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($staff ?? [] as $u)
                                <option value="{{ $u->id }}" {{ $supportTicket->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                @foreach(['low','normal','high'] as $p)
                                <option value="{{ $p }}" {{ $supportTicket->priority === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
