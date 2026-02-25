@extends('layouts.app')

@section('content')
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Welcome to Schtutz</h1>

        @auth
            <p class="text-gray-600">
                You’re logged in and ready to start. Use the sidebar to navigate.
            </p>
        @endauth

        @guest
            <p class="text-gray-600 mb-3">
                You are currently logged out.
            </p>

            <a href="{{ route('login') }}" class="btn btn-primary">
                Login
            </a>
        @endguest
    </div>
@endsection
