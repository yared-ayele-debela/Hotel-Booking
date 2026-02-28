@extends('admin.layouts.app')
@section('title', 'Review Moderation')
@section('content')
<div class="container-fluid">
    <x-page-title title="Review Moderation" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Reviews']]" />
    <x-alert />
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">Filter</label>
                    <select name="filter" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="pending" {{ request('filter') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('filter') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('filter') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="hidden" {{ request('filter') === 'hidden' ? 'selected' : '' }}>Hidden</option>
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
                    <tr><th>ID</th><th>Hotel</th><th>Rating</th><th>Comment</th><th>Approved</th><th>Hidden</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($reviews as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->booking->hotel->name ?? '-' }}</td>
                        <td>{{ $r->rating }}</td>
                        <td>{{ Str::limit($r->comment, 40) }}</td>
                        <td>{{ $r->approved ? 'Yes' : 'No' }}</td>
                        <td>{{ $r->hidden ? 'Yes' : 'No' }}</td>
                        <td><a href="{{ route('admin.reviews.show', $r) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-muted">No reviews.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
