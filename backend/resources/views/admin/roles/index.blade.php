@extends('admin.layouts.app')
@section('content')

    <div class="container-fluid">
        <x-page-title
            title="Roles"
            :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Roles']
    ]"
        />
    @can('create roles')
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary mb-3">Create Role</a>
    @endcan
    @if(session('success'))
        <div class="alert alert-success my-2">{{session('status')}} {{ session('success') }}</div>
    @endif
            <table class="table mt-3">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>
                            @foreach($role->permissions as $permission)
                                <span class="badge bg-primary">{{ $permission->name }}</span>
                            @endforeach
                        <td>
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this role?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{$roles->links()}}
    </div>
@endsection
