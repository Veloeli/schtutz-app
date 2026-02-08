@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Create Document</h2>

    <form method="POST" action="{{ route('documents.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ route('documents.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@endsection
