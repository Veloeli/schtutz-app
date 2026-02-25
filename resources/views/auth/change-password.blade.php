@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4">Change Password</h2>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Current Password</label>
            <input class="form-control" type="password" name="current_password" required>
        </div>

        <div class="mb-3">
            <label>New Password</label>
            <input class="form-control" type="password" name="password" required>
        </div>

        <div class="mb-3">
            <label>Confirm New Password</label>
            <input class="form-control" type="password" name="password_confirmation" required>
        </div>

        <button class="btn btn-primary w-100">Update</button>
    </form>
</div>
@endsection
