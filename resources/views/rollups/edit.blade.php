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

        @if ($rollup->parent_id)
        {{-- CODE (only for non-root nodes) --}}
        <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text"
                   name="code"
                   class="form-control"
                   value="{{ old('code', $rollup->code) }}">
        </div>

        <input type="text"
               name="user_id"
               class="form-control"
               value="{{ old('user_id', $rollup->user_id) }}"
               hidden>
        @else
        {{-- OWNER (only for root nodes) --}}
        <div class="mb-3">
            <label class="form-label">Owner</label>
            <select name="user_id" class="form-select" required>
                @foreach($possibleOwners as $user)
                    <option value="{{ $user->id }}"
                        {{ old('user_id', $rollup->user_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Team -->
        <div class="mb-4">
            <label class="form-label">Team</label>
            <select name="team_id" class="form-select">
                <option value="">— No team (private) —</option>

                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected($rollup->team_id == $team->id)>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @endif

        {{-- ACTIONS --}}
        <div class="d-flex justify-content-between align-items-center mt-4">

            <!-- Left side: Update + Cancel -->
            <div class="d-flex gap-2">
                @can('update', $rollup)
                <button type="submit" class="btn btn-primary">Update</button>
                @endcan

                <a href="{{ route('rollups.create', ['parent_id' => $rollup->id]) }}"
                   class="btn btn-primary">
                    Add Child
                </a>

                <a href="{{ route('rollups.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

            <!-- Right side: Delete (opens modal) -->
            @can('delete', $rollup)
            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal">
                Delete
            </button>
            @endcan
        </div>
    </form>
    <br>

    @if ($rollup->parent_id)
    {{-- BRANCH NODE : ASSIGN CATEGORIES --}}

        {{-- CATEGORY LIST --}}
        <h3 class="mt-4">Assigned Categories</h3>

        @if($assignedCategories->isEmpty())
            <p class="text-muted">No categories assigned to this node.</p>
        @else
            <ul class="list-group mb-3">
                @foreach($assignedCategories as $category)
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
        @if($availableCategories->isEmpty())
            <p class="text-muted">All categories are assigned in this rollup hierarchy.</p>
        @else
            <form action="{{ route('rollups.attachCategory', $rollup) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Select a category to add</label>

                    <div class="d-flex gap-2">
                        <select name="category_id" class="form-select flex-grow-1">
                            @foreach($availableCategories as $category)
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
