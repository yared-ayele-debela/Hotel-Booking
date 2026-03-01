@extends('admin.layouts.app')
@section('title', 'Add Hotel')
@section('content')
<div class="container-fluid">
    <x-page-title title="Add Hotel" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Hotels', 'url' => route('admin.vendor.hotels.index')], ['label' => 'Create']]" />
    <x-alert />
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.vendor.hotels.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Check-in (time)</label>
                        <input type="time" name="check_in" class="form-control" value="{{ old('check_in') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Check-out (time)</label>
                        <input type="time" name="check_out" class="form-control" value="{{ old('check_out') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cancellation policy</label>
                    <select name="cancellation_policy_preset" id="cancellation_policy_preset" class="form-select">
                        <option value="none" {{ old('cancellation_policy_preset', 'none') === 'none' ? 'selected' : '' }}>No policy</option>
                        <option value="non_refundable" {{ old('cancellation_policy_preset') === 'non_refundable' ? 'selected' : '' }}>Non-refundable</option>
                        <option value="free_24" {{ old('cancellation_policy_preset') === 'free_24' ? 'selected' : '' }}>Free cancellation until 24 hours before check-in</option>
                        <option value="free_48" {{ old('cancellation_policy_preset') === 'free_48' ? 'selected' : '' }}>Free cancellation until 48 hours before check-in</option>
                        <option value="free_168" {{ old('cancellation_policy_preset') === 'free_168' ? 'selected' : '' }}>Free cancellation until 7 days (168h) before check-in</option>
                        <option value="custom" {{ old('cancellation_policy_preset') === 'custom' ? 'selected' : '' }}>Custom (JSON)</option>
                    </select>
                    <div id="cancellation_policy_custom_wrap" class="mt-2" style="{{ old('cancellation_policy_preset') !== 'custom' ? 'display:none' : '' }}">
                        <label class="form-label small">Custom policy JSON</label>
                        <textarea name="cancellation_policy_custom" class="form-control font-monospace small" rows="6" placeholder='{"type":"rules","rules":[{"hours_before":168,"refund_percent":100},{"hours_before":48,"refund_percent":50}]}'>{{ old('cancellation_policy_custom') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Hotel</button>
                <a href="{{ route('admin.vendor.hotels.index') }}" class="btn btn-secondary">Cancel</a>
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
