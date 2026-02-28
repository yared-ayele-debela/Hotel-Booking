@extends('admin.layouts.app')
@section('content')
<div class="container-fluid">
    <x-page-title
        title="Users"
        :breadcrumbs="[
        ['label' => 'User', 'url' => route('admin.dashboard')],
        ['label' => 'Edit User']
    ]"
    />

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name"
                   value="{{ $user->name }}"
                   class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email"
                   value="{{ $user->email }}"
                   class="form-control" required>
        </div>

        <div class="mb-3">
            <label>New Password (optional)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Assign Roles</label><br>
            @foreach($roles as $role)
                <label>
                    <input type="checkbox"
                           name="roles[]"
                           value="{{ $role->name }}"
                        {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                    {{ $role->name }}
                </label><br>
            @endforeach
        </div>

        <button class="btn btn-success">Update User</button>
    </form>
</div>
@endsection
