@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Create Category</h2>

    <form method="POST" action="{{ route('categories.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Category name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="form-select">
                @foreach(\App\Models\Category::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(old('type') === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@endsection
