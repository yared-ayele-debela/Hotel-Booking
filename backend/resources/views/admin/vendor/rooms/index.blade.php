@extends('admin.layouts.app')
@section('title', 'Rooms & Availability')
@section('content')
<div class="container-fluid">
    <x-page-title title="Rooms & Availability" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Rooms']]" />
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
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
            </form>
        </div>
    </div>
    <a href="{{ route('admin.vendor.rooms.create', request()->only('hotel_id')) }}" class="btn btn-primary mb-3">Add Room</a>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr><th>Room</th><th>Hotel</th><th>Images</th><th>Capacity</th><th>Base price</th><th>Total rooms</th><th width="240">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($rooms as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->hotel->name ?? '-' }}</td>
                        <td>
                            @if($r->images && $r->images->count() > 0)
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info me-2">{{ $r->images->count() }} images</span>
                                    @if($r->bannerImage)
                                    <span class="badge bg-warning">Banner</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">No images</span>
                            @endif
                        </td>
                        <td>{{ $r->capacity }}</td>
                        <td>${{ number_format($r->base_price, 2) }}</td>
                        <td>{{ $r->total_rooms }}</td>
                        <td>
                            <a href="{{ route('admin.vendor.rooms.images.index', $r) }}" class="btn btn-sm btn-info me-1">Images</a>
                            <a href="{{ route('admin.vendor.rooms.availability', $r) }}" class="btn btn-sm btn-secondary me-1">Availability</a>
                            <a href="{{ route('admin.vendor.rooms.edit', $r) }}" class="btn btn-sm btn-warning me-1">Edit</a>
                            <form action="{{ route('admin.vendor.rooms.destroy', $r) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this room?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-muted">No rooms. <a href="{{ route('admin.vendor.rooms.create') }}">Add one</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $rooms->links() }}
        </div>
    </div>
</div>
@endsection
