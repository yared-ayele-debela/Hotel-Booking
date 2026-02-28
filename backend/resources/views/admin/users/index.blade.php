@extends('admin.layouts.app')
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Users"
        :breadcrumbs="[
        ['label' => 'Admin', 'url' => route('dashboard')],
        ['label' => 'Roles']
    ]"
    />
    @can('create users')
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary mb-3">Create User</a>
    @endcan
    <x-alert />

    <table class="table mt-3">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Roles</th>
            <th width="180">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge bg-info">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('admin.users.edit', $user->id) }}"
                       class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.users.destroy', $user->id) }}"
                          method="POST"
                          style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this user?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
