@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Categories</h2>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="bi bi-plus-circle"></i> Add Category
    </button>
</div>

<!-- Categories Table -->
<table class="table table-dark table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Actions</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
    @foreach ($categories as $category)
        <tr>
            <td>{{ $category->name }}</td>

            <td>
                @if ($category->is_private)
                    @if ($category->user_id === auth()->id())
                        <span class="badge bg-secondary">Private</span>
                    @else
                        <span class="badge bg-info">Shared with me</span>
                    @endif
                @else
                    <span class="badge bg-success">Public</span>
                @endif
            </td>

            <td style="width: 1%; white-space: nowrap;">
                <!-- Share -->
                @can('share', $category)
                    <button class="btn btn-sm btn-outline-warning"
                            data-bs-toggle="modal"
                            data-bs-target="#shareModal-{{ $category->id }}">
                        Share
                    </button>
                @endcan

                <!-- Unsubscribe -->
                @can('unsubscribe', $category)
                    <form action="{{ route('categories.unsubscribe', $category) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning">
                            Unsubscribe
                        </button>
                    </form>
                @endcan
            </td>

            <td style="width: 1%; white-space: nowrap;">
                <!-- Edit -->
                @can('update', $category)
                    <button class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#editCategoryModal-{{ $category->id }}">
                        Edit
                    </button>
                @endcan

                <!-- Delete -->
                @can('delete', $category)
                    <button class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteCategoryModal-{{ $category->id }}">
                        Delete
                    </button>
                @endcan
            </td>
        </tr>
    @endforeach
    </tbody>
</table>


<!-- SHARE MODALS -->
@foreach ($categories as $category)
@can('share', $category)
<div class="modal fade" id="shareModal-{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('categories.share', ['category' => $category->id]) }}" method="POST" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Share "{{ $category->name }}"</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label>Select user to share with:</label>
                <select name="user_id" class="form-select">
                    @foreach ($users as $user)
                        @if ($user->id !== auth()->id())
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Share</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endforeach


<!-- EDIT MODALS -->
@foreach ($categories as $category)
@can('update', $category)
<div class="modal fade" id="editCategoryModal-{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ $category->name }}"
                               required>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input"
                               type="checkbox"
                               name="is_private"
                               value="1"
                               id="editPrivateCheck-{{ $category->id }}"
                               {{ $category->is_private ? 'checked' : '' }}>
                        <label class="form-check-label" for="editPrivateCheck-{{ $category->id }}">
                            Private category
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>

            </form>

        </div>
    </div>
</div>
@endcan
@endforeach


<!-- DELETE MODALS -->
@foreach ($categories as $category)
@can('delete', $category)
<div class="modal fade" id="deleteCategoryModal-{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="modal-content">
            @csrf
            @method('DELETE')

            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete <strong>{{ $category->name }}</strong>?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endforeach


<!-- CREATE CATEGORY MODAL -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('categories.store') }}" method="POST" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Create Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Category name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_private"
                           value="1"
                           id="privateCheck"
                           checked>
                    <label class="form-check-label" for="privateCheck">
                        Private category
                    </label>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

@endsection
