@extends('admin.layouts.app')
@section('title', 'Business Details')
@section('content')
<div class="container-fluid">
    <x-page-title title="Business Details" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Business Details']]" />
    <x-alert />

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Business information</h5>
            <p class="text-muted small mb-4">Used for payouts, invoices, and platform records.</p>
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

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-2">Business documents</h5>
            <p class="text-muted small mb-4">Upload licenses, registration, tax certificates, or other supporting files (PDF, images, Word). Admins can view and download these from your vendor profile. Maximum 25 files total, up to 10 MB each.</p>

            @if($businessDocuments && count($businessDocuments) > 0)
            <div class="table-responsive mb-4">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th class="text-end" width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($businessDocuments as $doc)
                        <tr>
                            <td>
                                <i class="mdi mdi-file-document-outline me-1 text-primary"></i>
                                <span class="text-break">{{ $doc['original_name'] ?? basename($doc['path'] ?? '') }}</span>
                            </td>
                            <td class="text-muted small">
                                @if(!empty($doc['size']))
                                    {{ number_format($doc['size'] / 1024, 1) }} KB
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-muted small">
                                @if(!empty($doc['uploaded_at']))
                                    {{ \Carbon\Carbon::parse($doc['uploaded_at'])->format('M j, Y g:i a') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.vendor.profile.documents.download', ['documentId' => $doc['id']]) }}" class="btn btn-sm btn-outline-primary me-1">Download</a>
                                <form action="{{ route('admin.vendor.profile.documents.destroy', ['documentId' => $doc['id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted small mb-4">No documents uploaded yet.</p>
            @endif

            <h6 class="mb-3">Upload files</h6>
            <form action="{{ route('admin.vendor.profile.documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" name="document_files[]" class="form-control @error('document_files') is-invalid @enderror @error('document_files.*') is-invalid @enderror" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/*">
                    @error('document_files')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('document_files.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary" {{ count($businessDocuments ?? []) >= 25 ? 'disabled' : '' }}>Upload</button>
                @if(count($businessDocuments ?? []) >= 25)
                    <span class="text-muted small ms-2">Maximum number of files reached.</span>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Bank accounts</h5>
            <p class="text-muted small mb-4">Add one or more bank accounts for receiving payouts. The default account will be used unless specified otherwise.</p>

            @forelse($bankAccounts ?? [] as $bank)
            <div class="border rounded p-3 mb-3 position-relative">
                @if($bank->is_default)
                <span class="badge bg-primary position-absolute top-0 end-0 m-2">Default</span>
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>{{ $bank->account_holder_name }}</strong></p>
                        <p class="mb-1 text-muted small">{{ $bank->bank_name }} · {{ $bank->masked_account_number }} · {{ $bank->currency }}</p>
                        @if($bank->routing_number)<p class="mb-0 text-muted small">Routing: {{ $bank->routing_number }}</p>@endif
                        @if($bank->swift_code)<p class="mb-0 text-muted small">SWIFT: {{ $bank->swift_code }}</p>@endif
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBankModal{{ $bank->id }}">Edit</button>
                        <form method="POST" action="{{ route('admin.vendor.profile.bank-accounts.destroy', $bank) }}" class="d-inline" onsubmit="return confirm('Remove this bank account?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="editBankModal{{ $bank->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admin.vendor.profile.bank-accounts.update', $bank) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit bank account</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Account holder name</label>
                                        <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $bank->account_holder_name) }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Bank name</label>
                                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $bank->bank_name) }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Account number</label>
                                        <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $bank->account_number) }}" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Routing number</label>
                                            <input type="text" name="routing_number" class="form-control" value="{{ old('routing_number', $bank->routing_number) }}" placeholder="US/UK">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">SWIFT / BIC</label>
                                            <input type="text" name="swift_code" class="form-control" value="{{ old('swift_code', $bank->swift_code) }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Currency</label>
                                            <input type="text" name="currency" class="form-control" value="{{ old('currency', $bank->currency) }}" maxlength="3" placeholder="USD">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label d-block">Default for payouts</label>
                                            <div class="form-check mt-2">
                                                <input type="checkbox" name="is_default" value="1" class="form-check-input" {{ $bank->is_default ? 'checked' : '' }}>
                                                <label class="form-check-label">Use as default</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-muted mb-3">No bank accounts added yet.</p>
            @endforelse

            <hr class="my-4">
            <h6 class="mb-3">Add bank account</h6>
            <form action="{{ route('admin.vendor.profile.bank-accounts.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account holder name</label>
                        <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ old('account_holder_name') }}" required>
                        @error('account_holder_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank name</label>
                        <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}" required>
                        @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Account number</label>
                    <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number') }}" required>
                    @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Routing number</label>
                        <input type="text" name="routing_number" class="form-control @error('routing_number') is-invalid @enderror" value="{{ old('routing_number') }}" placeholder="US routing / UK sort code">
                        @error('routing_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">SWIFT / BIC</label>
                        <input type="text" name="swift_code" class="form-control @error('swift_code') is-invalid @enderror" value="{{ old('swift_code') }}">
                        @error('swift_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Currency</label>
                        <input type="text" name="currency" class="form-control @error('currency') is-invalid @enderror" value="{{ old('currency', 'USD') }}" maxlength="3">
                        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input" id="bank_is_default" {{ empty($bankAccounts) ? 'checked' : '' }}>
                        <label class="form-check-label" for="bank_is_default">Set as default for payouts</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add bank account</button>
            </form>
        </div>
    </div>
</div>
@endsection
