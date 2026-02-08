@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Categories</h2>

    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Category
    </a>
</div>

<!-- Categories Table -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Name</th>
            <th>Team</th>
            <th>Owner</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($categories as $category)
        <tr>
            <td>{{ str_repeat('— ', $category->depth) . $category->name }}</td>
            <td>
                @if ($category->team)
                    <span class="badge bg-primary">
                        {{ $category->team->name }}
                    </span>
                @else
                    <span class="text-muted">No team (private)</span>
                @endif
            </td>
            <td>
                {{ $category->owner->name }}
            </td>
            <td style="width: 1%; white-space: nowrap;">
                <!-- Edit (now a page) -->
                @can('update', $category)
                    <a href="{{ route('categories.edit', $category) }}"
                       class="btn btn-sm btn-outline-primary">
                        Edit
                    </a>
                @endcan

            </td>
        </tr>
    @endforeach
    </tbody>
</table>

@endsection
