@extends('layout.main')

@section('title', 'Edit User')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit User: {{ $user['username'] ?? '—' }}</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.update', $user['id']) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Bagian Detail Pengguna --}}
            <h5>User Details</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        value="{{ old('username', $user['username'] ?? '') }}"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        value="{{ old('email', $user['email'] ?? '') }}"
                        required
                    >
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="" disabled>Choose role...</option>
                        <option value="admin" {{ (old('role', $user['role'] ?? '') == 'admin') ? 'selected' : '' }}>Admin</option>
                        <option value="kasir" {{ (old('role', $user['role'] ?? '') == 'kasir') ? 'selected' : '' }}>Kasir / POS</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">

            {{-- Bagian Ganti Password --}}
            <h5>Change Password</h5>
            <small class="text-muted">(Leave blank to keep the current password)</small>
            <div class="row mt-2">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                    >
                </div>
            </div>

            {{-- Tampilkan error validation jika ada --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection
