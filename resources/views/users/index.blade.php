@extends('layout.main')

@section('title', 'User Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Create New User
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td>{{ $u['id'] }}</td>
                        <td>{{ $u['username'] }}</td>
                        <td>{{ $u['email'] }}</td>
                        <td>
                            <span class="badge 
                                {{ $u['role'] == 'admin' ? 'bg-primary' : 'bg-info' }}">
                                {{ ucfirst($u['role']) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('users.edit', $u['id']) }}"
                               class="btn btn-sm btn-outline-primary">
                               <i class="bi bi-pencil-square"></i>
                            </a>

                            <form action="{{ route('users.destroy', $u['id']) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
