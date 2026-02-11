@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Import SQL File</h1>

    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    <form action="/import" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf

        <div class="mb-3">
            <label for="sql_file" class="form-label">Choose SQL file</label>
            <input type="file" name="sql_file" id="sql_file" class="form-control" required>
        </div>

        <button class="btn btn-primary">Start Import</button>
    </form>
</div>
@endsection
