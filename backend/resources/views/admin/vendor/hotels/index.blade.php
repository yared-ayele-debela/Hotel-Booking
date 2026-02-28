@extends('admin.layouts.app')
@section('title', 'My Hotels')
@section('content')
<div class="container-fluid">
    <x-page-title title="My Hotels" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Hotels']]" />
    <x-alert />
    <a href="{{ route('admin.vendor.hotels.create') }}" class="btn btn-primary mb-3">Add Hotel</a>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr><th>Name</th><th>City</th><th>Country</th><th>Images</th><th>Status</th><th width="220">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($hotels as $h)
                    <tr>
                        <td>{{ $h->name }}</td>
                        <td>{{ $h->city }}</td>
                        <td>{{ $h->country }}</td>
                        <td>
                            @if($h->images && $h->images->count() > 0)
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info me-2">{{ $h->images->count() }} images</span>
                                    @if($h->bannerImage)
                                    <span class="badge bg-warning">Banner</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">No images</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $h->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $h->status }}</span></td>
                        <td>
                            <a href="{{ route('admin.vendor.hotels.images.index', $h) }}" class="btn btn-sm btn-info me-1">Images</a>
                            <a href="{{ route('admin.vendor.hotels.edit', $h) }}" class="btn btn-sm btn-warning me-1">Edit</a>
                            @if($h->status === 'active')
                            <form action="{{ route('admin.vendor.hotels.destroy', $h) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Deactivate this hotel?')">Deactivate</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-muted">No hotels yet. <a href="{{ route('admin.vendor.hotels.create') }}">Add one</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $hotels->links() }}
        </div>
    </div>
</div>
@endsection
