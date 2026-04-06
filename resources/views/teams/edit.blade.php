@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h2>Edit Team</h2>

    <form method="POST" action="{{ route('teams.update', $team) }}">
        @csrf
        @method('PUT')

        @cannot('update', $team)
            <fieldset disabled>
        @endcannot

        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $team->name) }}" required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Owner</label>
                @php
                    // Always include the owner
                    $user = $team->user;

                    // Combine owner + members, remove duplicates by ID
                    $selectableUsers = collect([$user])
                        ->merge($team->members)
                        ->unique('id');
                @endphp

                <select name="user_id" class="form-select" required>
                    @foreach($selectableUsers as $user)
                        <option value="{{ $user->id }}"
                            {{ old('user_id', $team->user_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Team can have common financials (categories)</label>
                <select name="has_common_financials" class="form-select">
                    <option value="0" @selected(!$team->has_common_financials)>No</option>
                    <option value="1" @selected($team->has_common_financials)>Yes</option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Team can use common reporting (rollups)</label>
                <select name="has_common_reporting" class="form-select">
                    <option value="0" @selected(!$team->has_common_reporting)>No</option>
                    <option value="1" @selected($team->has_common_reporting)>Yes</option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Team can use common securities</label>
                <select name="has_common_securities" class="form-select">
                    <option value="0" @selected(!$team->has_common_securities)>No</option>
                    <option value="1" @selected($team->has_common_securities)>Yes</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Team exists from</label>
            <input type="date"
                   name="valid_from"
                   class="form-control"
                   value="{{ old('valid_from', optional($team->valid_from)->format('Y-m-d')) }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Team exists until</label>
            <input type="date"
                   name="valid_until"
                   class="form-control"
                   value="{{ old('valid_until', optional($team->valid_until)->format('Y-m-d')) }}">
            </div>
        </div>

        @cannot('update', $team)
            </fieldset>
        @endcannot

        <div class="d-flex justify-content-between mt-4">

            <div class="d-flex gap-2">
                @can('update', $team)
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                @endcan

                <a href="{{ route('teams.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>

            @can('delete', $team)
            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal">
                Delete
            </button>
            @endcan
        </div>
    </form>

    <hr class="my-4">

    <h4 class="mb-3">Team Members</h4>

    @if($team->members->isEmpty())
        <p class="text-muted">This team has no members yet.</p>
    @else
        <ul class="list-group mb-3">
            @foreach($team->members as $member)

                @php
                    // Fetch the real TeamUser model for this member
                    $membership = $team->memberships
                        ->firstWhere('user_id', $member->id);
                @endphp

                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $member->name }}</strong><br>
                        <small class="text-muted">
                            {{ $member->email }}

                            • Reveal Private: <strong>{{ $membership->reveal_private ? 'Yes' : 'No' }}</strong>

                            • Sharing Ratio: <strong>{{ $membership->sharing_ratio + 0 }} of {{ $team->sharing_ratio_sum + 0}}</strong>

                            @if ($membership->clearingAccount)
                                • Clearing Account: <strong>{{ $membership->clearingAccount->name }}</strong>
                            @endif

                            @if($membership->member_from !== null) 
                                • Member from: <strong>{{ $membership->member_from->format('d.m.Y') }}</strong>
                            @endif                    

                            @if($membership->member_to !== null) 
                                • Member until: <strong>{{ $membership->member_to->format('d.m.Y') }}</strong>
                            @endif                    

                       </small>
                    </div>

                    @can('update', $membership)
                        <div class="d-flex gap-2">
                            <a id="edit-membership-{{ $member->id }}"
                               href="{{ route('teams.memberships.edit', [$team, $membership]) }}"
                               class="btn btn-sm btn-primary"
                               >
                                Edit
                            </a>
                        </div>
                    @endcan
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Add Member --}}
    <form action="{{ route('teams.memberships.store', $team) }}" method="POST" class="d-flex gap-2">
        @csrf
        <input type="email"
               name="email"
               class="form-control"
               placeholder="User email"
               required>
        <button class="btn btn-primary">Add</button>
    </form>
</div>

<!-- DELETE TEAM MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete Team</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $team->name }}</strong>?</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <form method="POST" action="{{ route('teams.destroy', $team) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
