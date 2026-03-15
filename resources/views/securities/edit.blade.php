@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Security</h2>

    <form action="{{ route('securities.update', $security) }}" method="POST" class="mb-4">
        @csrf
        @method('PUT')
        @include('securities.partials.form')

        <div class="d-flex justify-content-between mt-4">
            <div class="d-flex gap-2">
                <!-- Save -->
                <button type="submit" class="btn btn-primary">Update</button>

                <a href="{{ route('securities.index') }}"
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

<!-- DELETE SECURITY MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete Security</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $security->name }}</strong>?</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <form id="deleteDocumentForm"
                      method="POST"
                      action="{{ route('securities.destroy', $security) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete Security</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
