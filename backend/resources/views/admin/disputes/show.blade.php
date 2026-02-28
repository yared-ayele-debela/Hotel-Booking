@extends('admin.layouts.app')
@section('title', 'Dispute #' . $dispute->id)
@section('content')
<div class="container-fluid">
    <x-page-title title="Dispute #{{ $dispute->id }}" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Disputes', 'url' => route('admin.disputes.index')], ['label' => 'View']]" />
    <x-alert />
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Booking</h5></div>
                <div class="card-body">
                    <p class="mb-1"><strong>UUID:</strong> {{ $dispute->booking->uuid ?? $dispute->booking->id }}</p>
                    <p class="mb-1"><strong>Hotel:</strong> {{ $dispute->booking->hotel->name ?? '-' }}</p>
                    <p class="mb-1"><strong>Customer:</strong> {{ $dispute->booking->customer->name ?? $dispute->booking->customer->email ?? '-' }}</p>
                    <p class="mb-1"><strong>Check-in / Check-out:</strong> {{ $dispute->booking->check_in?->format('Y-m-d') }} – {{ $dispute->booking->check_out?->format('Y-m-d') }}</p>
                    <p class="mb-0"><strong>Total:</strong> ${{ number_format($dispute->booking->total_price ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Contact</h5></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> {{ $dispute->contact_name ?: '-' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $dispute->contact_email ?: '-' }}</p>
                    <p class="mb-0"><strong>Phone:</strong> {{ $dispute->contact_phone ?: '-' }}</p>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Customer notes</h5></div>
                <div class="card-body">{{ $dispute->customer_notes ?: '-' }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Update dispute</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.disputes.update', $dispute) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach(['open','in_review','resolved','closed'] as $s)
                                <option value="{{ $s }}" {{ $dispute->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Internal notes</label>
                            <textarea name="internal_notes" class="form-control" rows="4">{{ old('internal_notes', $dispute->internal_notes) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <p class="mb-0"><strong>Resolved:</strong> {{ $dispute->resolved_at ? $dispute->resolved_at->format('Y-m-d H:i') : '-' }}</p>
                    <p class="mb-0"><strong>By:</strong> {{ $dispute->resolvedBy->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
