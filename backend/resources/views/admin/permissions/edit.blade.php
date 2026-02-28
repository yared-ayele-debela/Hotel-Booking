@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <x-page-title
            title="Edit Permission"
            :breadcrumbs="[
        ['label' => 'Permissions', 'url' => route('admin.permissions.index')],
        ['label' => 'Edit Permission']
    ]"
        />
    <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Permission Name</label>
            <input type="text" name="name" value="{{ $permission->name }}" class="form-control" required>
        </div>

        <button class="btn btn-success mt-3">Update Permission</button>
    </form>
@endsection
