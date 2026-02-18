@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Edit Rollup Node</h2>

    <br>

    <form method="POST" action="{{ route('rollups.update', $rollup->id) }}">
        @csrf
        @method('PUT')

        {{-- NAME --}}
        <div class="mb-3">
            <label class="form-label">{{ $rollup->parent_id ? 'Name' : 'Hierarchy Name (Top Node)' }}</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   value="{{ old('name', $rollup->name) }}"
                   required>
        </div>
        @if ($rollup->parent_id)
        <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text"
                   name="code"
                   class="form-control"
                   value="{{ old('code', $rollup->code) }}"
                   required>
        </div>
        @endif

        <div class="d-flex justify-content-between mt-4">

            <!-- Left side: Update + Cancel -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                
                <a href="{{ route('rollups.index') }}"
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

        <br>
        <hr class="my-4">

        <h4>Child Nodes</h4>

        @if ($children->isEmpty())
            <p class="text-muted">This rollup has no children.</p>
        @else
            <ul class="list-group">
                @foreach ($children as $child)
                    <li class="list-group-item d-flex align-items-center">
                        <!-- LEFT: Name -->
                        <div class="flex-grow-1">
                            <span class="fw-bold">{{ $child->name }}</span>
                        </div>

                        <!-- MIDDLE: Code -->
                        <div class="flex-grow-0" style="min-width: 50px;">
                            <span class="text-muted small">{{ $child->code }}</span>
                        </div>

                        <!-- RIGHT: Button -->
                        <div class="ms-auto">
                            <a href="{{ route('rollups.edit', ['rollup' => $child->id]) }}"
                               class="btn btn-sm btn-primary">
                                Edit
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <br>

        <a href="{{ route('rollups.create', ['parent_id' => $rollup->id]) }}"
           class="btn btn-primary">
            Add Child
        </a>

    </form>
</div>

<!-- DELETE ROLLUP MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete Node</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Delete node <strong id="rollupName">{{ $rollup->name }}</strong>?</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <form id="deleteRollupForm" 
                    method="POST"
                    action="{{ route('rollups.destroy', ['rollup' => $rollup->id]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
