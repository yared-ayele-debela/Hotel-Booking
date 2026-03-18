@extends('admin.layouts.app')
@section('title', 'Generate Payouts')
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Generate Payouts"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Payouts', 'url' => route('admin.payouts.index')],
            ['label' => 'Generate']
        ]"
    />
    <x-alert />

    <div class="card">
        <div class="card-body">
            <p class="text-muted">Create payouts for a period. Only confirmed bookings with check-in dates in the period that haven't been paid out yet will be included. One payout per vendor.</p>
            <form method="POST" action="{{ route('admin.payouts.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label for="period_start" class="form-label">Period start (check-in date)</label>
                    <input type="date" name="period_start" id="period_start" class="form-control @error('period_start') is-invalid @enderror" value="{{ old('period_start') }}" required>
                    @error('period_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="period_end" class="form-label">Period end (check-in date)</label>
                    <input type="date" name="period_end" id="period_end" class="form-control @error('period_end') is-invalid @enderror" value="{{ old('period_end') }}" required>
                    @error('period_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Generate payouts</button>
                    <a href="{{ route('admin.payouts.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
