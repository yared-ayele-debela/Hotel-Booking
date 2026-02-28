@extends('admin.layouts.app')
@section('title', 'Edit Room')
@section('content')
<div class="container-fluid">
    <x-page-title title="Edit Room" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Rooms', 'url' => route('admin.vendor.rooms.index')], ['label' => 'Edit']]" />
    <x-alert />
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.vendor.rooms.update', $room) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Room name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $room->name) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Capacity *</label>
                        <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity) }}" min="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Total rooms *</label>
                        <input type="number" name="total_rooms" class="form-control" value="{{ old('total_rooms', $room->total_rooms) }}" min="1" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Base price *</label>
                    <input type="number" name="base_price" class="form-control" step="0.01" value="{{ old('base_price', $room->base_price) }}" min="0" required>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.vendor.rooms.availability', $room) }}" class="btn btn-info">Availability</a>
                <a href="{{ route('admin.vendor.rooms.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
