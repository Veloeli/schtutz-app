@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h2>Edit Membership in Team "{{ $team->name }}"</h2>

    <form method="POST" action="{{ route('teams.memberships.update', [$team, $membership]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Member</label>
            <input type="text" class="form-control" value="{{ $membership->user->name }} ({{ $membership->user->email }})" disabled>
        </div>

        @if($team->has_common_financials)
            <div class="mb-3">
                <label class="form-label">Sharing Ratio</label>
                <input type="number" name="sharing_ratio" class="form-control"
                       value="{{ old('sharing_ratio', $membership->sharing_ratio) }}"
                       min="0" max="100" step="0.01">
            </div>

            <div class="mb-3">
                <label class="form-label">Reveal Private</label>
                <select name="reveal_private" class="form-select" id="reveal_private">
                    <option value="0" @selected(old('reveal_private', $membership->reveal_private) == 0)>No</option>
                    <option value="1" @selected(old('reveal_private', $membership->reveal_private) == 1)>Yes</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Clearing Account</label>
                <select name="clearing_account" class="form-select">
                    <option value="">— Kein Clearing Account —</option>
                    @foreach($clearingAccounts as $cat)
                        <option value="{{ $cat->id }}"
                            @selected(old('clearing_account', $membership->clearing_account) == $cat->id)>
                            {{ $cat->code }} — {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Member from</label>
                <input type="date"
                       name="member_from"
                       class="form-control"
                       value="{{ old('member_from', optional($membership->member_from)->format('Y-m-d')) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Member until</label>
                <input type="date" 
                       name="member_to" 
                       class="form-control"
                       value="{{ old('member_to', optional($membership->member_to)->format('Y-m-d')) }}">
            </div>
        </div>


        <div class="d-flex justify-content-between mt-4">

            <!-- Left side: Update + Cancel -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>

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
