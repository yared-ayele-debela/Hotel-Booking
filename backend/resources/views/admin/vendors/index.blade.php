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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $v)
                        <tr>
                            <td>{{ $v->name }}</td>
                            <td>{{ $v->email }}</td>
                            <td>
                                <span class="badge {{ $v->status === 'active' ? 'bg-success' : 'bg-warning' }}">{{ $v->status }}</span>
                            </td>
                            <td>
                                @if($v->status === 'suspended')
                                <form action="{{ route('admin.vendors.update-status', $v) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                @else
                                <form action="{{ route('admin.vendors.update-status', $v) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="suspended">
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Suspend this vendor?')">Suspend</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted">No vendors</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $vendors->links() }}
        </div>
    </div>
</div>
@endsection
