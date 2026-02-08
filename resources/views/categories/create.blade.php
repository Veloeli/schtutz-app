@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Create Category</h2>

    <form method="POST" action="{{ route('categories.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Category name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@endsection
