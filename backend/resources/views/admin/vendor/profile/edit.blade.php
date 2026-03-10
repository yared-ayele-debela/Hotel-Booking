@extends('admin.layouts.app')
@section('title', 'Business Details')
@section('content')
<div class="container-fluid">
    <x-page-title title="Business Details" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Business Details']]" />
    <x-alert />

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-4">Update your business information. This is used for payouts, invoices, and platform records.</p>
            <form action="{{ route('admin.vendor.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Business name</label>
                        <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror"
                               value="{{ old('business_name', $profile->business_name) }}" placeholder="Your hotel or company name">
                        @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tax ID / VAT number</label>
                        <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror"
                               value="{{ old('tax_id', $profile->tax_id) }}" placeholder="e.g. VAT123456789">
                        @error('tax_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Business address</label>
                    <input type="text" name="business_address" class="form-control @error('business_address') is-invalid @enderror"
                           value="{{ old('business_address', $profile->business_address) }}" placeholder="Street, city, country">
                    @error('business_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="business_phone" class="form-control @error('business_phone') is-invalid @enderror"
                               value="{{ old('business_phone', $profile->business_phone) }}" placeholder="+1 234 567 8900">
                        @error('business_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Website</label>
                        <input type="text" name="business_website" class="form-control @error('business_website') is-invalid @enderror"
                               value="{{ old('business_website', $profile->business_website) }}" placeholder="https://example.com">
                        @error('business_website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Additional details</label>
                    <textarea name="business_details" class="form-control @error('business_details') is-invalid @enderror" rows="3"
                              placeholder="Registration number, business type, or any other relevant information">{{ old('business_details', $profile->business_details) }}</textarea>
                    @error('business_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="{{ route('admin.vendor.dashboard') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
