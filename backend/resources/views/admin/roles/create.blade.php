@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
    <x-page-title
        title="Create Role"
        :breadcrumbs="[
        ['label' => 'Roles', 'url' => route('admin.roles.index')],
        ['label' => 'Create Role']
    ]"
    />

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Role Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <h5 class="mt-3">Assign Permissions</h5>
        <div class="row">
            @foreach($permissions->groupBy(fn($p) => explode(' ', $p->name)[1]) as $group => $perms)
                <div class="col-md-3">
                    <h6 class="text-capitalize">{{ $group }}</h6>
                    @foreach($perms as $permission)
                        <label>
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}">
                            {{ $permission->name }}
                        </label><br>
                    @endforeach
                </div>
            @endforeach
        </div>

        <button class="btn btn-primary mt-3">Create Role</button>
    </form>
    </div>
@endsection
