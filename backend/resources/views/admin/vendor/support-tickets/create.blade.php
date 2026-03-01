@extends('admin.layouts.app')
@section('title', 'New Support Ticket')
@section('content')
<div class="container-fluid">
    <x-page-title title="New support ticket" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Support', 'url' => route('admin.vendor.support-tickets.index')], ['label' => 'New']]" />
    <x-alert />
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.vendor.support-tickets.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        @foreach(\App\Enums\TicketCategory::cases() as $c)
                        <option value="{{ $c->value }}" {{ old('category') === $c->value ? 'selected' : '' }}>{{ $c->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required maxlength="255">
                    @error('subject')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="body" class="form-control" rows="5" required maxlength="10000">{{ old('body') }}</textarea>
                    @error('body')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low" {{ old('priority', 'normal') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit ticket</button>
                <a href="{{ route('admin.vendor.support-tickets.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
