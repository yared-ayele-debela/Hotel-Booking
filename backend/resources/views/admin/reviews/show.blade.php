@extends('admin.layouts.app')
@section('title', 'Review')
@section('content')
<div class="container-fluid">
    <x-page-title title="Review #{{ $review->id }}" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Reviews', 'url' => route('admin.reviews.index')], ['label' => 'View']]" />
    <x-alert />
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Review</h5></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Rating:</strong> {{ $review->rating }}</p>
                    <p class="mb-1"><strong>Comment:</strong></p>
                    <p class="mb-2">{{ $review->comment ?: '-' }}</p>
                    <p class="mb-1"><strong>Hotel:</strong> {{ $review->booking->hotel->name ?? '-' }}</p>
                    <p class="mb-0"><strong>Customer:</strong> {{ $review->booking->customer->name ?? $review->booking->customer->email ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Moderate</h5></div>
                <div class="card-body">
                    <p class="mb-2">Status: {{ $review->approved ? 'Approved' : 'Not approved' }} | Hidden: {{ $review->hidden ? 'Yes' : 'No' }}</p>
                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-warning btn-sm">Reject</button>
                    </form>
                    @if($review->hidden)
                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="unhide">
                        <button type="submit" class="btn btn-info btn-sm">Unhide</button>
                    </form>
                    @else
                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="hide">
                        <button type="submit" class="btn btn-secondary btn-sm">Hide</button>
                    </form>
                    @endif
                </div>
            </div>
            @if($review->moderated_at)
            <p class="text-muted small">Moderated: {{ $review->moderated_at->format('Y-m-d H:i') }} by {{ $review->moderatedBy->name ?? '-' }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
