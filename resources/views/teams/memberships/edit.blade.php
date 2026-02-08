@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h2>Edit Membership in Team "{{ $team->name }}"</h2>

    <form method="POST" action="{{ route('teams.memberships.update', [$team, $membership]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label fw-bold">Member</label>
            <input type="text" class="form-control" value="{{ $membership->user->name }} ({{ $membership->user->email }})" disabled>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Reveal Private</label>
                <select name="reveal_private" class="form-select">
                    <option value="0" @selected(!$membership->reveal_private)>No</option>
                    <option value="1" @selected($membership->reveal_private)>Yes</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Sharing Ratio</label>
                <input type="number" name="sharing_ratio" class="form-control"
                       value="{{ old('sharing_ratio', $membership->sharing_ratio) }}" step="any">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Clearing Account</label>
            <input type="text" name="clearing_account" class="form-control"
                   value="{{ old('clearing_account', $membership->clearing_account) }}">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Member From</label>
                <input type="date" name="member_from" class="form-control"
                       value="{{ old('member_from', $membership->member_from) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Member To</label>
                <input type="date" name="member_to" class="form-control"
                       value="{{ old('member_to', $membership->member_to) }}">
            </div>
        </div>


        <div class="d-flex justify-content-between mt-4">

            <!-- Left side: Update + Cancel -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>

                <a href="{{ route('teams.index') }}"
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

<!-- DELETE TEAM MEMBER MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete Membership in Team</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Remove member <strong>{{ $membership->user->email }}</strong>?</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <form id="deleteMemberForm" 
                    method="POST"
                    action="{{ route('teams.memberships.destroy', [$team, $membership]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Remove</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
