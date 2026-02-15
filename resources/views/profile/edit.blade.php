@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Profile</h2>
</div>

{{-- PERSONAL INFORMATION --}}
<div class="card mb-4">
    <div class="card-header">
        Personal Information
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name', auth()->user()->name) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">E-Mail</label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email', auth()->user()->email) }}">
            </div>

            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

{{-- DEPUTIES --}}
<div class="card mb-4">
    <div class="card-header">
        Deputies
    </div>

    <div class="card-body">

        {{-- List deputies --}}
        @if(auth()->user()->deputies->isEmpty())
            <p class="text-muted">No deputies assigned.</p>
        @else
            <ul class="list-group">
                @foreach(auth()->user()->deputies as $deputy)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $deputy->name }}</strong><br>
                            <small class="text-muted">{{ $deputy->email }}</small>
                        </div>

                        <form method="POST" action="{{ route('profile.deputies.destroy', $deputy) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
        <p>

        {{-- Add deputy --}}
        <form action="{{ route('profile.deputies.store') }}" method="POST" class="d-flex gap-2">
            @csrf
            <input type="email"
                   name="email"
                   class="form-control"
                   placeholder="User email"
                   required>
            <button class="btn btn-primary">Add</button>
        </form>

    </div>
</div>

@endsection
