@extends('admin.layouts.app')
@section('title', 'Amenities')
@section('content')
<div class="container-fluid">
    <x-page-title title="Amenities" :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Amenities']]" />
    <x-alert />
    <a href="{{ route('admin.amenities.create') }}" class="btn btn-primary mb-3">Add Amenity</a>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Sort</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Icon</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($amenities as $amenity)
                    <tr>
                        <td>{{ $amenity->sort_order }}</td>
                        <td>{{ $amenity->name }}</td>
                        <td><code>{{ $amenity->slug }}</code></td>
                        <td>{{ $amenity->icon ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.amenities.edit', $amenity) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.amenities.destroy', $amenity) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this amenity?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-muted">No amenities yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $amenities->links() }}
        </div>
    </div>
</div>
@endsection
