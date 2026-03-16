@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">Create Stock Split</h1>

    <form action="{{ route('stock-splits.store') }}" method="POST" class="mb-4">
        @csrf

        {{-- Pass old_id explicitly --}}
        <input type="hidden" name="old_id" value="{{ $split->oldId }}">

        @include('stock-splits.partials.form-fields')

        <button class="btn btn-primary">Save</button>

        <a href="{{ route('securities.edit', $split->old_id) }}" class="btn btn-secondary">
            Cancel
        </a>
    </form>
</div>
@endsection
