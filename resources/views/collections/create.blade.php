@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Create Collection</h2>

    <form method="POST" action="{{ route('collections.store') }}">
        @csrf

        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label">Collection name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control">
            </div>
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ route('collections.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@endsection
