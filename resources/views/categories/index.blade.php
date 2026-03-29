@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Categories</h2>

    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New
    </a>
</div>

<form method="GET" action="{{ route('categories.index') }}" class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <!-- User/Team Selector -->
    <x-user-team-selector
        :action="route('categories.index')"
        :teamfilter="$teamfilter"
    />
</form>

<!-- Categories Table -->
@if($categories->isEmpty())
    <p class="text-muted">
        No categories yet.
    </p>
@else
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Category</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
        @foreach ($categories as $category)
            <tr>
                <td>
                    @if(!$category->is_selectable)<del>@endif
                    {{ $category->code }} {{ $category->name }}
                    @if($category->is_selectable)</del>@endif

                    <div class="text-muted small">
                        {{ $category->type_label }}
                        @if ($category->source_label)
                            <span class="badge bg-secondary" id="badge-{{ $category->id }}">
                                {{ $category->source_label }}
                            </span>
                        @endif
                    </div>
                </td>
                <td style="width: 1%; white-space: nowrap;">
                    @can('update', $category)
                        <a id="edit-category-{{ $category->id }}"
                           href="{{ route('categories.edit', $category) }}"
                           class="btn btn-sm btn-primary">
                            Edit
                        </a>
                    @else
                        <span id="user-category-{{ $category->id }}" class="text-muted">
                            
                        </span>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@endsection
