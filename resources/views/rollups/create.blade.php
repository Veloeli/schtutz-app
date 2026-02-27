@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>{{ $parent ? 'Create Child Node' : 'Create new Rollup Hierarchy' }}</h2>

    <br>

    <form method="POST"
          action="{{ $parent
                ? route('rollups.storeChild', $parent->id)
                : route('rollups.storeRoot') }}">

        @csrf

        {{-- SHOW PARENT IF PRESENT --}}
        @if ($parent)
        <div class="mb-3">
            <label class="form-label">Parent</label>
            <input type="text"
                   class="form-control"
                   value="{{ $parent->name }}"
                   disabled>
        </div>
        @endif

        {{-- NAME --}}
        <div class="mb-3">
            <label class="form-label">{{ $parent ? 'Child Name' : 'Hierarchy Name (Top Node)' }}</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   required>
        </div>

        @if ($parent)
        <div class="mb-3">
            <label class="form-label">Child Code</label>
            <input type="text"
                   name="code"
                   class="form-control">
        </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save</button>

                <a href="{{ route('rollups.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
