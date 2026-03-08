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
                @if(isset($amenities) && $amenities->isNotEmpty())
                <div class="mb-3">
                    <label class="form-label">Room amenities</label>
                    <div class="row">
                        @foreach($amenities as $a)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input type="checkbox" name="amenities[]" value="{{ $a->id }}" id="amenity_{{ $a->id }}" class="form-check-input" {{ in_array($a->id, old('amenities', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="amenity_{{ $a->id }}">{{ $a->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Cancellation policy</label>
                    <select name="cancellation_policy_preset" id="cancellation_policy_preset" class="form-select">
                        <option value="none" {{ old('cancellation_policy_preset', 'none') === 'none' ? 'selected' : '' }}>Use hotel default</option>
                        <option value="non_refundable" {{ old('cancellation_policy_preset') === 'non_refundable' ? 'selected' : '' }}>Non-refundable</option>
                        <option value="free_24" {{ old('cancellation_policy_preset') === 'free_24' ? 'selected' : '' }}>Free cancellation until 24 hours before check-in</option>
                        <option value="free_48" {{ old('cancellation_policy_preset') === 'free_48' ? 'selected' : '' }}>Free cancellation until 48 hours before check-in</option>
                        <option value="free_168" {{ old('cancellation_policy_preset') === 'free_168' ? 'selected' : '' }}>Free cancellation until 7 days (168h) before check-in</option>
                        <option value="custom" {{ old('cancellation_policy_preset') === 'custom' ? 'selected' : '' }}>Custom (JSON)</option>
                    </select>
                    <div id="cancellation_policy_custom_wrap" class="mt-2" style="{{ old('cancellation_policy_preset') !== 'custom' ? 'display:none' : '' }}">
                        <label class="form-label small">Custom policy JSON</label>
                        <textarea name="cancellation_policy_custom" class="form-control font-monospace small" rows="6" placeholder='{"type":"rules","rules":[...]}'>{{ old('cancellation_policy_custom') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Room</button>
                <a href="{{ route('admin.vendor.rooms.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('cancellation_policy_preset').addEventListener('change', function() {
    document.getElementById('cancellation_policy_custom_wrap').style.display = this.value === 'custom' ? '' : 'none';
});
</script>
@endsection
