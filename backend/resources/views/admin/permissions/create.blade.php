@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="container-fluid">
            <x-page-title
                title="Create Permission"
                :breadcrumbs="[
        ['label' => 'Permission', 'url' => route('dashboard')],
        ['label' => 'Create Permission']
    ]"
            />>
        <form action="{{ route('admin.permissions.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Permission Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <button class="btn btn-primary mt-3">Create Permission</button>
        </form>
    </div>
@endsection
