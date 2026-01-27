@extends('layouts.app')

@section('content')
<h2 class="mb-4">Categories</h2>

<form action="{{ route('categories.store') }}" method="POST" class="mb-4">
    @csrf
    <div class="input-group">
        <input type="text" name="name" class="form-control" placeholder="New category">
        <button class="btn btn-primary">Add</button>
    </div>
</form>

<table class="table table-dark table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th style="width: 150px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($categories as $category)
        <tr>
            <td>{{ $category->name }}</td>
            <td>
                <form action="{{ route('categories.update', $category) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $category->name }}" class="form-control d-inline w-50">
                    <button class="btn btn-sm btn-success">Save</button>
                </form>

                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
