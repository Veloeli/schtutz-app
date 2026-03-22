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
<form method="GET" action="{{ route('rollups.index') }}" class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <x-root-selector />
    @php($selectedRoot = $component->selectedRoot)
</form>

{{-- Rollup tree --}}
@if ($selectedRoot)
    <div class="ms-3">
        @include('rollups.partials.node', ['node' => $selectedRoot])
    </div>
@endif

@endsection
