@extends('admin.layouts.app')
@section('title', 'Add Country')
@section('content')
<div class="container-fluid">
    <x-page-title title="Add Country" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Countries', 'url' => route('admin.countries.index')], ['label' => 'Create']]" />
    <x-alert />
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.countries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="e.g. US">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Default tax rate (%)</label>
                        <input type="number" name="tax_rate" class="form-control" value="{{ old('tax_rate') }}" min="0" max="100" step="0.01" placeholder="10">
                        <small class="text-muted">Used when hotel has no tax rate.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Default tax name</label>
                        <input type="text" name="tax_name" class="form-control" value="{{ old('tax_name') }}" placeholder="Occupancy tax / VAT">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('admin.countries.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
