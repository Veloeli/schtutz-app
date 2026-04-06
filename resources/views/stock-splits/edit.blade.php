@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">Edit Stock Split</h1>

    <form action="{{ route('stock-splits.update', $split) }}" method="POST" class="mb-4">
        @csrf
        @method('PUT')

        @include('stock-splits.partials.form-fields')

        <div class="d-flex justify-content-between mt-4">
            <div class="d-flex gap-2">
                <!-- Save -->
                <button type="submit" class="btn btn-primary">Save Changes</button>

                <a href="{{ route('securities.edit', $split->old_id) }}" 
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

            <!-- Right side: Delete (opens modal) -->
            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal">
                Delete
            </button>
        </div>
    </form>
</div>

<!-- DELETE STOCK SPLIT MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete Stock Split</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to delete the split of <strong>{{ $split->split_date->format('d.m.Y') }}</strong>?</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <form id="deleteSplitForm"
                      method="POST"
                      action="{{ route('stock-splits.destroy', $split) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete Split</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
