{{-- Geoapify location autocomplete for hotel address/lat/lng --}}
<div class="mb-3">
    <label class="form-label">Search location</label>
    <div class="position-relative">
        <input type="text" id="location_search" class="form-control" placeholder="Start typing address or place name..." autocomplete="off">
        <div id="location_suggestions" class="list-group position-absolute w-100 shadow-sm rounded mt-1" style="z-index: 1050; display: none; max-height: 280px; overflow-y: auto;"></div>
    </div>
    <small class="text-muted">Search for your hotel address. Selecting a result will fill address, city, country, and coordinates.</small>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" name="address" id="hotel_address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', optional($hotel ?? null)->address ?? '') }}" placeholder="Street address">
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Coordinates</label>
        <div class="d-flex align-items-center gap-2">
            <input type="hidden" name="latitude" id="hotel_latitude" value="{{ old('latitude', optional($hotel ?? null)->latitude ?? '') }}">
            <input type="hidden" name="longitude" id="hotel_longitude" value="{{ old('longitude', optional($hotel ?? null)->longitude ?? '') }}">
            <span id="coordinates_display" class="text-muted small">—</span>
        </div>
    </div>
</div>
