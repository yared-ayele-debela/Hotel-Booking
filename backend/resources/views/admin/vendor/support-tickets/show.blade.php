@extends('admin.layouts.app')
@section('title', 'Ticket #' . $supportTicket->id)
@section('content')
<div class="container-fluid">
    <x-page-title title="Ticket #{{ $supportTicket->id }}" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Support', 'url' => route('admin.vendor.support-tickets.index')], ['label' => 'View']]" />
    <x-alert />
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $supportTicket->subject }}</h5>
            <span class="badge bg-secondary">{{ $supportTicket->status }}</span>
        </div>
        <div class="card-body">
            <p class="mb-1"><strong>Category:</strong> {{ \App\Enums\TicketCategory::tryFrom($supportTicket->category)?->label() ?? $supportTicket->category }} | <strong>Priority:</strong> {{ $supportTicket->priority }}</p>
            <p class="mb-2"><strong>Created:</strong> {{ $supportTicket->created_at->format('Y-m-d H:i') }}</p>
            <div class="border rounded p-2 bg-light">{{ $supportTicket->body }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Replies</h5></div>
        <div class="card-body">
            @forelse($supportTicket->replies as $reply)
            <div class="border rounded p-2 mb-2 {{ $reply->user_id === $supportTicket->user_id ? 'bg-light' : '' }}">
                <small class="text-muted">{{ $reply->user->name ?? $reply->user->email }} — {{ $reply->created_at->format('Y-m-d H:i') }}</small>
                <div class="mt-1">{{ $reply->body }}</div>
            </div>
            @empty
            <p class="text-muted mb-0">No replies yet. Support will respond here.</p>
            @endforelse
        </div>
    </div>
    <div class="mt-2">
        <a href="{{ route('admin.vendor.support-tickets.index') }}" class="btn btn-outline-secondary">Back to list</a>
    </div>
</div>
@endsection
