@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Edit Rollup Node</h2>

    <br>

    {{-- UPDATE ROLLUP FORM --}}
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

        {{-- CODE (only for non-root nodes) --}}
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

        {{-- ACTIONS --}}
        <div class="d-flex justify-content-between align-items-center mt-4">

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
    </form>
    <br>

    @if (!$rollup->parent_id)
    {{-- TOP NODE : ASSIGN USERS --}}

        {{-- USER LIST --}}
        <h3 class="mt-4">Assigned Users</h3>

        @if($assignedUsers->isEmpty())
            <p class="text-muted">Hierarchy not shared with anyone.</p>
        @else
            <ul class="list-group mb-3">
                @foreach($assignedUsers as $user)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            {{ $user->name }} — {{ $user->email }}
                        </span>

                        {{-- DETACH USER FORM --}}
                        <form action="{{ route('rollups.detachUser', [$rollup, $user]) }}"
                              method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- ADD USER --}}
        <form action="{{ route('rollups.attachUser', $rollup) }}" method="POST">
            @csrf
            <div class="mb-3">

                <div class="d-flex gap-2">
                    <input type="hidden" name="rollup_id" value="{{ $rollup->id }}">

                    <input type="email"
                           name="email"
                           class="form-control"
                           placeholder="User email"
                           required>

                    <button class="btn btn-primary">Add</button>
                </div>
            </div>
        </form>

    @else
    {{-- BRANCH NODE : ASSIGN CATEGORIES --}}

        {{-- CATEGORY LIST --}}
        <h3 class="mt-4">Assigned Categories</h3>

        @if($assigned->isEmpty())
            <p class="text-muted">No categories assigned to this node.</p>
        @else
            <ul class="list-group mb-3">
                @foreach($assigned as $category)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            {{ $category->code }} — {{ $category->name }}
                            @if($category->team_id)
                                <span class="badge bg-secondary">
                                    {{ $category->team->name }}
                                </span>
                            @elseif($category->user_id !== auth()->id())
                                <span class="badge bg-secondary">
                                    {{ $category->owner->name }}
                                </span>
                            @endif
                        </span>

                        {{-- DETACH CATEGORY FORM --}}
                        <form action="{{ route('rollups.detachCategory', [$rollup, $category]) }}"
                              method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- ADD CATEGORY --}}
        <br>
        @if($available->isEmpty())
            <p class="text-muted">All categories are assigned in this rollup hierarchy.</p>
        @else
            <form action="{{ route('rollups.attachCategory', $rollup) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Select a category to add</label>

                    <div class="d-flex gap-2">
                        <select name="category_id" class="form-select flex-grow-1">
                            @foreach($available as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->code }} — {{ $category->name }}
                                @if($category->team_id)
                                    <span class="badge bg-secondary">
                                        [{{ $category->team->name }}]
                                    </span>
                                @elseif($category->user_id !== auth()->id())
                                    <span class="badge bg-secondary">
                                        [{{ $category->owner->name }}]
                                    </span>
                                @endif
                                </option>
                            @endforeach
                        </select>

                        <button class="btn btn-primary">Add</button>
                    </div>
                </div>
            </form>
        @endif
    @endif

</div>

{{-- DELETE ROLLUP MODAL --}}
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

                {{-- DELETE ROLLUP FORM --}}
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
