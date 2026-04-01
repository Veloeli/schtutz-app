@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">Edit Category</h2>

    <form action="{{ route('categories.update', $category) }}" method="POST" class="mb-4">
        @csrf
        @method('PUT')

        <!-- Name -->
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   value="{{ old('name', $category->name) }}"
                   required>
        </div>

        <!-- Code (optional legacy) -->
        <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text"
                   name="code"
                   class="form-control"
                   value="{{ old('code', $category->code) }}">
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="form-select">
                @foreach(\App\Models\Category::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected($category->type === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Selectable -->
        <div class="mb-3">
            <label class="form-label">Category is in use</label>
            <select name="is_selectable" class="form-select">
                <option value="0" @selected(!$category->is_selectable)>No</option>
                <option value="1" @selected($category->is_selectable)>Yes</option>
            </select>
        </div>

        <!-- Team -->
        <div class="mb-4">
            <label class="form-label">Team</label>
            <select name="team_id" class="form-select">
                <option value="">— No team (private) —</option>

                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected($category->team_id == $team->id)>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <div class="d-flex gap-2">
                <!-- Save -->
                <button type="submit" class="btn btn-primary">Update</button>

                <a href="{{ route('categories.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

            <!-- Right side: Delete (opens modal) -->
            @can('delete', $category)
            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal">
                Delete
            </button>
            @endcan
        </div>
    </form>

</div>

<!-- DELETE CATEGORY MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $category->name }}</strong>?</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <form id="deleteCategoryForm"
                      method="POST"
                      action="{{ route('categories.destroy', $category) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
