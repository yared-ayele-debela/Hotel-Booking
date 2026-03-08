@extends('admin.layouts.app')
@section('title', 'Cities')
@section('content')
<div class="container-fluid">
    <x-page-title title="Cities" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Cities']]" />
    <x-alert />
    <a href="{{ route('admin.cities.create') }}" class="btn btn-primary mb-3">Add City</a>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cities as $city)
                    <tr>
                        <td>
                            @if($city->image)
                                <img src="{{ asset('storage/'.$city->image) }}" alt="" class="rounded" style="height:40px;width:60px;object-fit:cover">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $city->name }}</td>
                        <td>{{ $city->country->name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.cities.destroy', $city) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this city?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted">No cities yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $cities->links() }}
        </div>
    </div>
</div>
@endsection
