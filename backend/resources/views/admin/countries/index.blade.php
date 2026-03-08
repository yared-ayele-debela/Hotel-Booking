@extends('admin.layouts.app')
@section('title', 'Countries')
@section('content')
<div class="container-fluid">
    <x-page-title title="Countries" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Countries']]" />
    <x-alert />
    <a href="{{ route('admin.countries.create') }}" class="btn btn-primary mb-3">Add Country</a>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Cities</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($countries as $country)
                    <tr>
                        <td>
                            @if($country->image)
                                <img src="{{ asset('storage/'.$country->image) }}" alt="" class="rounded" style="height:40px;width:60px;object-fit:cover">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $country->name }}</td>
                        <td>{{ $country->code ?? '—' }}</td>
                        <td>{{ $country->cities_count }}</td>
                        <td>
                            <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.countries.destroy', $country) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this country?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-muted">No countries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $countries->links() }}
        </div>
    </div>
</div>
@endsection
