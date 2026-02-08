@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Create Team</h2>

    <form method="POST" action="{{ route('teams.store') }}">
        @csrf

        <div class="mb-3">
                    <label for="name" class="form-label">Team Name</label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required>

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ route('teams.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

</div>
@endsection
