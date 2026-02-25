@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4">Login</h2>

    @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label>Email</label>
            <input class="form-control" type="email" name="email" required autofocus>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input class="form-control" type="password" name="password" required>
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button class="btn btn-primary w-100">Login</button>

        <div class="mt-3 text-center">
            <a href="{{ route('password.request') }}">Forgot your password?</a>
        </div>

        <div class="mt-3 text-center">
            <a href="{{ route('register') }}">Create an account</a>
        </div>
    </form>
</div>
@endsection
