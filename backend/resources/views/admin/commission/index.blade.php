@extends('admin.layouts.app')
@section('title', 'Commission')
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Commission"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Commission']
        ]"
    />
    <x-alert />

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Current rate</h5>
                    <p class="display-6">{{ number_format($rate * 100, 1) }}%</p>
                    <a href="{{ route('admin.commission.edit') }}" class="btn btn-primary">Edit rate</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Commission by vendor</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2"><a href="{{ route('admin.payouts.index') }}">Manage payouts</a> to process vendor payments.</p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Contact</th>
                                    <th>Business</th>
                                    <th>Payout bank</th>
                                    <th class="text-end">ID</th>
                                    <th class="text-end">Bookings</th>
                                    <th class="text-end">Gross</th>
                                    <th class="text-end">Commission</th>
                                    <th class="text-end">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $row)
                                <tr>
                                    <td>
                                        @if(!empty($row['vendor_name']))
                                            <a href="{{ route('admin.vendors.show', $row['vendor_id']) }}">{{ $row['vendor_name'] }}</a>
                                            @if(!empty($row['vendor_profile_status']))
                                                <span class="badge bg-secondary ms-1">{{ $row['vendor_profile_status'] }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if(!empty($row['vendor_email']))
                                            <div><a href="mailto:{{ $row['vendor_email'] }}">{{ $row['vendor_email'] }}</a></div>
                                        @endif
                                        @if(!empty($row['business_phone']))
                                            <div class="text-muted">{{ $row['business_phone'] }}</div>
                                        @endif
                                        @if(empty($row['vendor_email']) && empty($row['business_phone']))
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if(!empty($row['business_name']))
                                            <div>{{ $row['business_name'] }}</div>
                                        @endif
                                        @if(!empty($row['tax_id']))
                                            <div class="text-muted">Tax ID: {{ $row['tax_id'] }}</div>
                                        @endif
                                        @if(!empty($row['business_address']))
                                            <div class="text-muted">{{ \Illuminate\Support\Str::limit($row['business_address'], 80) }}</div>
                                        @endif
                                        @if(empty($row['business_name']) && empty($row['tax_id']) && empty($row['business_address']))
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if(!empty($row['bank_name']))
                                            <div>{{ $row['bank_name'] }}</div>
                                            @if(!empty($row['bank_account_masked']))
                                                <div class="text-muted">{{ $row['bank_account_masked'] }} @if(!empty($row['bank_currency']))<span class="text-uppercase">({{ $row['bank_currency'] }})</span>@endif</div>
                                            @endif
                                            @if(!empty($row['bank_account_holder']))
                                                <div class="text-muted">{{ $row['bank_account_holder'] }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">No bank on file</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">{{ $row['vendor_id'] }}</td>
                                    <td class="text-end">{{ $row['booking_count'] }}</td>
                                    <td class="text-end">${{ number_format((float) $row['gross'], 2) }}</td>
                                    <td class="text-end">${{ number_format((float) $row['commission'], 2) }}</td>
                                    <td class="text-end"><strong>${{ number_format((float) $row['net'], 2) }}</strong></td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-muted">No data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
