@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4">Reset Password</h2>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label>Email</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $request->email) }}" required>
        </div>

        <div class="mb-3">
            <label>New Password</label>
            <input class="form-control" type="password" name="password" required>
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <input class="form-control" type="password" name="password_confirmation" required>
        </div>

        <button class="btn btn-primary w-100">Reset Password</button>
    </form>
</div>
@endsection
