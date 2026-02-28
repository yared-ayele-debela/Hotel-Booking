@extends('admin.layouts.app')
@section('title', 'Add Room')
@section('content')
<div class="container-fluid">
    <x-page-title title="Add Room" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Rooms', 'url' => route('admin.vendor.rooms.index')], ['label' => 'Create']]" />
    <x-alert />
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.vendor.rooms.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Hotel *</label>
                    <select name="hotel_id" class="form-select @error('hotel_id') is-invalid @enderror" required>
                        <option value="">Select hotel</option>
                        @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ old('hotel_id', $hotelId) == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                    @error('hotel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Room name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Capacity *</label>
                        <input type="number" name="capacity" class="form-control" value="{{ old('capacity', 1) }}" min="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Total rooms *</label>
                        <input type="number" name="total_rooms" class="form-control" value="{{ old('total_rooms', 1) }}" min="1" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Base price *</label>
                    <input type="number" name="base_price" class="form-control" step="0.01" value="{{ old('base_price') }}" min="0" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Room</button>
                <a href="{{ route('admin.vendor.rooms.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
