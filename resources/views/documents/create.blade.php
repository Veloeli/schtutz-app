@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Create Document</h2>

    <br>
    <form method="POST" action="{{ route('documents.store') }}">
        @csrf

        {{-- TITLE --}}
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text"
                   name="title"
                   class="form-control"
                   required>
        </div>

        {{-- META ROW --}}
        <div class="mb-3">
            <label class="form-label">Posting Date</label>
            <input type="date"
                   name="posting_date"
                   class="form-control"
                   value="{{ $posting_date }}"
                   required>
        </div>
        
        <br>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('documents.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
