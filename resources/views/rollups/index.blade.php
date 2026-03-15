@extends('layouts.app')

@section('content')
<div class="container-fluid">

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Rollup Hierarchies</h2>

    <a href="{{ route('rollups.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New 
    </a>
</div>

{{-- Root selector --}}
<form method="GET" action="{{ route('rollups.index') }}" name="dropdown" class="mb-4">
    <div class="w-auto" style="max-width: 300px;">
        <select name="root_id" id="root_id" class="form-select" onchange="this.form.submit()">
            @foreach ($rootRollups as $root)
                <option value="{{ $root->id }}" @selected($selectedRoot && $selectedRoot->id === $root->id)>
                    {{ $root->name }}
                </option>
            @endforeach
        </select>
    </div>
</form>

{{-- Rollup tree --}}
@if ($selectedRoot)
    <div class="ms-3">
        @include('rollups.partials.node', ['node' => $selectedRoot])
    </div>
@endif

@endsection
