@extends('admin.layouts.app')
@section('title', 'Payouts')
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Payouts"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Payouts']
        ]"
    />
    <x-alert />

    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('admin.payouts.create') }}" class="btn btn-primary">
            <i data-feather="plus" class="icon-sm me-1"></i>Generate payouts
        </a>
        <a href="{{ route('admin.payouts.export') }}?{{ request()->getQueryString() }}" class="btn btn-outline-secondary">
            <i data-feather="download" class="icon-sm me-1"></i>Export CSV
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Vendor</label>
                    <select name="vendor_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }} ({{ $v->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor</th>
                        <th>Period</th>
                        <th>Gross</th>
                        <th>Commission</th>
                        <th>Net</th>
                        <th>Status</th>
                        <th>Reference</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>
                            <a href="{{ route('admin.vendors.show', $p->vendor_id) }}">{{ $p->vendor->name ?? $p->vendor_id }}</a>
                            <br><small class="text-muted">{{ $p->vendor->email ?? '' }}</small>
                        </td>
                        <td>{{ $p->period_start->format('M d, Y') }} – {{ $p->period_end->format('M d, Y') }}</td>
                        <td>${{ number_format($p->amount, 2) }}</td>
                        <td>${{ number_format($p->commission, 2) }}</td>
                        <td><strong>${{ number_format($p->net, 2) }}</strong></td>
                        <td>
                            @if($p->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($p->status === 'processing')
                                <span class="badge bg-info">Processing</span>
                            @else
                                <span class="badge bg-success">Paid</span>
                            @endif
                        </td>
                        <td>{{ $p->reference ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.payouts.show', $p) }}" class="btn btn-sm btn-outline-primary">View</a>
                            @if(!$p->isPaid())
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $p->id }}">Mark paid</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-muted">No payouts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $payouts->links() }}
        </div>
    </div>

    @foreach($payouts as $p)
    @if(!$p->isPaid())
    <div class="modal fade" id="markPaidModal{{ $p->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.payouts.mark-paid', $p) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Mark payout #{{ $p->id }} as paid</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Reference (optional)</label>
                            <input type="text" name="reference" class="form-control" placeholder="Bank transfer ref, PayPal ID, etc.">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Mark as paid</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection
