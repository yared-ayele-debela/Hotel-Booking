@extends('admin.layouts.app')
@section('content')
    <h1>Role: {{ $role->name }}</h1>
    <h5>Permissions:</h5>
    <ul>
        @foreach($role->permissions as $permission)
            <li>{{ $permission->name }}</li>
        @endforeach
    </ul>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Back</a>
@endsection
