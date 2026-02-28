@extends('admin.layouts.app')
@section('title', 'Edit Commission Rate')
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Edit Commission Rate"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Commission', 'url' => route('admin.commission.index')],
            ['label' => 'Edit']
        ]"
    />
    <x-alert />

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.commission.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="commission_rate" class="form-label">Commission rate (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="commission_rate" id="commission_rate"
                                   class="form-control @error('commission_rate') is-invalid @enderror"
                                   value="{{ old('commission_rate', $rate * 100) }}" required>
                            @error('commission_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('admin.commission.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
