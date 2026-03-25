@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Profile</h2>

    <a href="{{ route('password.change') }}" class="btn btn-primary">
        Change Password
    </a>
</div>


{{-- PERSONAL INFORMATION --}}
<form method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PATCH')
    <div class="card mb-4">
        <div class="card-header">
            Personal Information
        </div>

        <div class="card-body">
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
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Preferences
        </div>
    
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Preferred Rollup Hierarchy</label>
                <select name="preferred_root_id" class="form-select">
                    <option value="">— None —</option>

                    @foreach($rootRollups as $root)
                        <option value="{{ $root->id }}"
                            @selected(old('preferred_root_id', auth()->user()->preferred_root_id) == $root->id)>
                            {{ $root->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Freeze Documents after (months)</label>
                <input type="number"
                       step="1"
                       name="freeze_after"
                       class="form-control"
                       value="{{ old('freeze_after', auth()->user()->freeze_after) }}">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <div class="d-flex gap-2">
            <!-- Save -->
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </div>
</form>

@endsection
