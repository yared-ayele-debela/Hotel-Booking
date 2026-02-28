@extends('admin.layouts.app')
@section('title', 'Room Availability')
@section('content')
<div class="container-fluid">
    <x-page-title title="Availability" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Rooms', 'url' => route('admin.vendor.rooms.index')], ['label' => 'Availability']]" />
    <x-alert />
    <p class="text-muted">Room: {{ $room->name }}. Hotel: {{ $room->hotel->name ?? '-' }}. Base price: ${{ number_format($room->base_price, 2) }}.</p>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Add or update date</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.vendor.rooms.availability.store', $room) }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-auto">
                    <label class="form-label mb-0">Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Available rooms</label>
                    <input type="number" name="available_rooms" class="form-control" min="0" value="0" required>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Price override ($)</label>
                    <input type="number" name="price_override" class="form-control" step="0.01" min="0" placeholder="Leave empty for base">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr><th>Date</th><th>Available rooms</th><th>Price override</th></tr>
                </thead>
                <tbody>
                    @forelse($availability as $a)
                    <tr>
                        <td>{{ $a->date->format('Y-m-d') }}</td>
                        <td>{{ $a->available_rooms }}</td>
                        <td>{{ $a->price_override ? '$' . number_format($a->price_override, 2) : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-muted">No availability rows. Use the form above to add dates.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $availability->links() }}
        </div>
    </div>
    <a href="{{ route('admin.vendor.rooms.index') }}" class="btn btn-secondary mt-2">Back to Rooms</a>
</div>
@endsection
