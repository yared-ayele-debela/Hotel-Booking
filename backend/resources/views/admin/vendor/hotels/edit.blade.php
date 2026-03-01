@extends('admin.layouts.app')
@section('title', 'Edit Hotel')
@section('content')
<div class="container-fluid">
    <x-page-title title="Edit Hotel" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Hotels', 'url' => route('admin.vendor.hotels.index')], ['label' => 'Edit']]" />
    <x-alert />
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.vendor.hotels.update', $hotel) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $hotel->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status', $hotel->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $hotel->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $hotel->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $hotel->address) }}">
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $hotel->city) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $hotel->country) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Check-in</label>
                        <input type="time" name="check_in" class="form-control" value="{{ old('check_in', $hotel->check_in) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Check-out</label>
                        <input type="time" name="check_out" class="form-control" value="{{ old('check_out', $hotel->check_out) }}">
                    </div>
                </div>
                @php
                    $cp = $hotel->cancellation_policy;
                    $cpPreset = old('cancellation_policy_preset');
                    if ($cpPreset === null && $cp) {
                        if (($cp['type'] ?? '') === 'non_refundable') { $cpPreset = 'non_refundable'; }
                        elseif (($cp['type'] ?? '') === 'free_until_hours') {
                            $h = (int)($cp['hours'] ?? 0);
                            $cpPreset = $h === 24 ? 'free_24' : ($h === 48 ? 'free_48' : ($h === 168 ? 'free_168' : 'custom'));
                        } else { $cpPreset = 'custom'; }
                    } elseif ($cpPreset === null) { $cpPreset = 'none'; }
                    $cpCustom = old('cancellation_policy_custom', $cpPreset === 'custom' && is_array($cp) ? json_encode($cp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '');
                @endphp
                <div class="mb-3">
                    <label class="form-label">Cancellation policy</label>
                    <select name="cancellation_policy_preset" id="cancellation_policy_preset" class="form-select">
                        <option value="none" {{ $cpPreset === 'none' ? 'selected' : '' }}>No policy</option>
                        <option value="non_refundable" {{ $cpPreset === 'non_refundable' ? 'selected' : '' }}>Non-refundable</option>
                        <option value="free_24" {{ $cpPreset === 'free_24' ? 'selected' : '' }}>Free cancellation until 24 hours before check-in</option>
                        <option value="free_48" {{ $cpPreset === 'free_48' ? 'selected' : '' }}>Free cancellation until 48 hours before check-in</option>
                        <option value="free_168" {{ $cpPreset === 'free_168' ? 'selected' : '' }}>Free cancellation until 7 days (168h) before check-in</option>
                        <option value="custom" {{ $cpPreset === 'custom' ? 'selected' : '' }}>Custom (JSON)</option>
                    </select>
                    <div id="cancellation_policy_custom_wrap" class="mt-2" style="{{ $cpPreset !== 'custom' ? 'display:none' : '' }}">
                        <label class="form-label small">Custom policy JSON</label>
                        <textarea name="cancellation_policy_custom" class="form-control font-monospace small" rows="6" placeholder='{"type":"rules","rules":[{"hours_before":168,"refund_percent":100},{"hours_before":48,"refund_percent":50},{"hours_before":0,"refund_percent":0}]}'>{{ $cpCustom }}</textarea>
                        <small class="text-muted">e.g. type: "non_refundable" | "free_until_hours" (with "hours") | "rules" (with "rules": [{"hours_before": int, "refund_percent": int}])</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
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
