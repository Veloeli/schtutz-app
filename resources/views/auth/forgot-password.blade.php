@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4">Forgot Password</h2>

    <p class="text-muted">Enter your email and we’ll send you a reset link.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label>Email</label>
            <input class="form-control" type="email" name="email" required autofocus>
        </div>

        <button class="btn btn-primary w-100">Send Reset Link</button>
    </form>
</div>
@endsection
