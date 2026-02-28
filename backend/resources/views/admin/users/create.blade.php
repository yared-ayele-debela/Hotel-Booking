@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">

        <x-page-title
            title="Users"
            :breadcrumbs="[
        ['label' => 'User', 'url' => route('admin.dashboard')],
        ['label' => 'Create User']
    ]"
        />

        <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Assign Roles</label><br>
            @foreach($roles as $role)
                <label>
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}">
                    {{ $role->name }}
                </label><br>
            @endforeach
        </div>

        <button class="btn btn-primary">Create User</button>
    </form>
    </div>
@endsection
