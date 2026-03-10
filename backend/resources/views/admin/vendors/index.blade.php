@extends('admin.layouts.app')
@section('title', 'Vendors')
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Vendors"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Vendors']
        ]"
    />
    <x-alert />

    <div class="mb-3">
        <a href="{{ route('admin.vendors.index', ['status' => '']) }}" class="btn btn-outline-secondary btn-sm">All</a>
        <a href="{{ route('admin.vendors.index', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-sm">Pending</a>
        <a href="{{ route('admin.vendors.index', ['status' => 'approved']) }}" class="btn btn-outline-success btn-sm">Approved</a>
        <a href="{{ route('admin.vendors.index', ['status' => 'rejected']) }}" class="btn btn-outline-danger btn-sm">Rejected</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Business</th>
                            <th>Approval</th>
                            <th>Account</th>
                            <th width="280">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $v)
                        @php
                            $profile = $v->vendorProfile;
                            $approvalStatus = $profile?->status ?? 'pending';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.vendors.show', $v) }}">{{ $v->name }}</a>
                            </td>
                            <td>{{ $v->email }}</td>
                            <td>
                                {{ $profile?->business_name ?? '—' }}
                                @if($profile && ($profile->business_phone || $profile->business_address))
                                    <br><small class="text-muted">{{ $profile->business_phone ?? '' }}{{ $profile->business_phone && $profile->business_address ? ' · ' : '' }}{{ Str::limit($profile->business_address ?? '', 30) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($approvalStatus === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($approvalStatus === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $v->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $v->status }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.vendors.show', $v) }}" class="btn btn-sm btn-outline-primary me-1">View</a>
                                @if($approvalStatus === 'pending')
                                    <form action="{{ route('admin.vendors.approve', $v) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $v->id }}">Reject</button>
                                    <div class="modal fade" id="rejectModal{{ $v->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.vendors.reject', $v) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject vendor</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <label class="form-label">Reason (optional)</label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Reason for rejection"></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($approvalStatus === 'approved' && $v->status === 'active')
                                    <form action="{{ route('admin.vendors.update-status', $v) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="suspended">
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Suspend this vendor?')">Suspend</button>
                                    </form>
                                @elseif($v->status === 'suspended')
                                    <form action="{{ route('admin.vendors.update-status', $v) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-muted">No vendors</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $vendors->links() }}
        </div>
    </div>
</div>
@endsection
