@extends('layouts.app')

@section('content')

<div class="container py-4">

    <h2 class="mb-4">Edit Document</h2>

    <form method="POST" action="{{ route('documents.update', $document) }}">
        @csrf
        @method('PUT')

        {{-- TITLE --}}
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text"
                   name="title"
                   class="form-control"
                   value="{{ old('title', $document->title) }}"
                   required>
        </div>

        {{-- META ROW --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Posting Date</label>
                <input type="date"
                       name="posting_date"
                       class="form-control"
                       value="{{ old('posting_date', $document->posting_date) }}"
                       required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Document Owner</label>
                <input type="text"
                       class="form-control"
                       value="{{ $document->owner->name }}"
                       disabled>
            </div>
        </div>

        <hr class="my-4">

        {{-- REPEAT SETTINGS --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Repeat every ... months</label>
                <input type="number"
                       name="repeat_pattern"
                       class="form-control"
                       min="0"
                       max="24"
                       value="{{ old('repeat_pattern', $document->repeat_pattern) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Repeat with values (amount/quantity)</label>
                <select name="repeat_constant" class="form-select">
                    <option value="1" {{ old('repeat_constant', $document->repeat_constant) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('repeat_constant', $document->repeat_constant) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="d-flex justify-content-between align-items-center mt-4">

            <div class="d-flex gap-2">
                @can('update', $document)
                <button type="submit" class="btn btn-primary">
                    Update
                </button>
                @endcan

                <a href="{{ route('documents.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

            @can('delete', $document)
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

{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">

            <div class="modal-header">
                <h5 class="modal-title">Delete Document</h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>
                    Are you sure you want to delete
                    <strong>{{ $document->title }}</strong>?
                </p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                @can('delete', $document)
                <form method="POST"
                      action="{{ route('documents.destroy', $document) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">
                        Delete
                    </button>
                </form>
                @endcan
            </div>

        </div>
    </div>
</div>

{{-- DISABLE repeat_constant --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const months = document.querySelector('input[name="repeat_pattern"]');
    const constant = document.querySelector('select[name="repeat_constant"]');

    function updateState() {
        const value = parseInt(months.value || 0);

        if (value === 0) {
            constant.disabled = true;
        } else {
            constant.disabled = false;
        }
    }

    months.addEventListener('input', updateState);
    updateState(); // run once on page load
});
</script>

@endsection
