@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <x-page-title
            title="Edit Role"
            :breadcrumbs="[
        ['label' => 'Roles', 'url' => route('admin.roles.index')],
        ['label' => 'Edit Role']
    ]"
        />

    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Role Name</label>
            <input type="text" name="name" value="{{ $role->name }}" class="form-control" required>
        </div>

        <h5 class="mt-3">Assign Permissions</h5>
        <div class="row">
            @foreach($permissions->groupBy(fn($p) => explode(' ', $p->name)[1]) as $group => $perms)
                <div class="col-md-3">
                    <h6 class="text-capitalize">{{ $group }}</h6>
                    @foreach($perms as $permission)
                        <label>
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                            {{ $permission->name }}
                        </label><br>
                    @endforeach
                </div>
            @endforeach
        </div>

        <button class="btn btn-success mt-3">Update Role</button>
    </form>
    </div>

@endsection
