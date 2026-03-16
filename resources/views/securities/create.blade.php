@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Create Security</h2>

    <form action="{{ route('securities.store') }}" method="POST">
        @csrf
        @include('securities.partials.form-fields')

        <button class="btn btn-primary">Save</button>
        <a href="{{ route('securities.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
