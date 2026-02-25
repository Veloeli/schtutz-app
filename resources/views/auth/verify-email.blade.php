@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4">Verify Your Email</h2>

    <p class="text-muted">
        Before continuing, please check your email for a verification link.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">
            A new verification link has been sent to your email.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn-primary w-100">Resend Verification Email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button class="btn btn-secondary w-100">Logout</button>
    </form>
</div>
@endsection
