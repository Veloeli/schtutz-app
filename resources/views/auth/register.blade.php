@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4">Register</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input class="form-control" type="text" name="name" required autofocus>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input class="form-control" type="email" name="email" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input class="form-control" type="password" name="password" required>
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <input class="form-control" type="password" name="password_confirmation" required>
        </div>

        <button class="btn btn-primary w-100">Create Account</button>
    </form>
</div>
@endsection
