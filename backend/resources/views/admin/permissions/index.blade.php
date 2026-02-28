
@extends('admin.layouts.app')
@section('content')
<div class="container-fluid">
    <div class="container-fluid">
        <x-page-title
            title="Permissions"
            :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Permissions']
    ]"
        />
    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary mb-3">Create Permission</a>
    @if(session('success'))
       <x-alert />
    @endif

    <table class="table mt-3">
        <thead>
        <tr>
            <th>Name</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($permissions as $permission)
            <tr>
                <td>{{ $permission->name }}</td>
                <td>
                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this permissions?')">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$permissions->links()}}
</div>
@endsection
