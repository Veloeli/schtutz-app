@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4">Confirm Password</h2>

    <p class="text-muted">
        For your security, please confirm your password to continue.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label>Password</label>
            <input class="form-control" type="password" name="password" required autofocus>
        </div>

        <button class="btn btn-primary w-100">Confirm</button>
    </form>
</div>
@endsection
