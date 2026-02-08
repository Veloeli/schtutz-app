@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">My Teams</h2>

    <a href="{{ route('teams.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Team
    </a>
</div>

<table class="table table-hover">
    <thead>
        <tr>
            <th>Team</th>
            <th>Members</th>
            <th width="120">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($teams as $team)
            <tr>
                <td>
                    <strong>{{ $team->name }}</strong><br>
                    <small class="text-muted">{{ $team->description }}</small>
                </td>

                <td>
                    @foreach ($team->members as $user)
                        <span class="badge bg-light text-dark me-1">
                            {{ $user->email }}
                        </span>
                    @endforeach
                </td>

                <td style="width: 1%; white-space: nowrap;">
                    @can('update', $team)
                    <a href="{{ route('teams.edit', $team) }}" class="btn btn-primary btn-sm">
                        Edit
                    </a>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center py-4 text-muted">
                    You don’t have any teams yet.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<br /> 
<h2 class="mt-4">My Memberships</h2>

<table class="table table-hover">
    <thead>
        <tr>
            <th>Team</th>
            <th>Reveal Private</th>
            <th>Share</th>
            <th>Valid from</th>
            <th>Valid to</th>
            <th>Clearing</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($memberships as $membership)
            <tr>
                <td>{{ $membership->team->name }}</td>
                <td>{{ $membership->reveal_private ? 'Yes' : 'No' }}</td>
                <td>{{ $membership->sharing_ratio + 0 }} of {{ $membership->team->sharing_ratio_sum + 0}}</td>
                <td>{{ $membership->member_from }}</td>
                <td>{{ $membership->member_to }}</td>
                <td>{{ $membership->clearing_account }}</td>
                <td style="width: 1%; white-space: nowrap;">
                        <a href="{{ route('teams.memberships.edit', [$membership->team->id, $membership->id]) }}"
                           class="btn btn-sm btn-primary">
                            Edit
                        </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-muted">
                    You are not a member of any team.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection
