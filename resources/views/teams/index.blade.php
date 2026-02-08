@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Teams</h2>

    <a href="{{ route('teams.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Team
    </a>
</div>

@if($ownedTeamsNotMember->isNotEmpty())
    <ul class="list-group mb-4">
        @foreach($ownedTeamsNotMember as $team)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $team->name }}</strong><br>
                    <small class="text-muted">You are the owner</small>

                    {{-- MEMBER BADGES --}}
                    <div class="mt-1">
                        @foreach($team->members as $member)
                            <span class="badge bg-secondary me-1">
                                {{ $member->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('teams.edit', $team) }}" class="btn btn-primary btn-sm">
                    Edit
                </a>
            </li>
        @endforeach
    </ul>
@endif


@if($memberships->isEmpty())
    <p class="text-muted">
        You are not a member of any team.
    </p>
@else
    <ul class="list-group mb-3">
        @foreach($memberships as $membership)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $membership->team->name  }}</strong><br>
                    <small class="text-muted">
                        Reveal Private: <strong>{{ $membership->reveal_private ? 'Yes' : 'No' }}</strong>

                        • Sharing Ratio: <strong>{{ $membership->sharing_ratio + 0 }} of {{ $membership->team->sharing_ratio_sum + 0}}</strong>

                        @if($membership->clearing_account !== null) 
                            • Clearing: <strong>{{ $membership->clearing_account }}</strong>
                        @endif                    

                        @if($membership->member_from !== null) 
                            • Member from: <strong>{{ $membership->member_from }}</strong>
                        @endif                    

                        @if($membership->member_to !== null) 
                            • Member until: <strong>{{ $membership->member_to }}</strong>
                        @endif                    
                    </small>

                    {{-- MEMBER BADGES --}}
                    <div class="mt-1">
                        @foreach($membership->team->members as $member)
                            <span class="badge bg-secondary me-1">
                                {{ $member->name }}
                            </span>
                        @endforeach
                    </div>

                </div>

                @can('update', $membership)
                    <div class="d-flex gap-2">
                        <a href="{{ route('teams.edit', $membership->team) }}" class="btn btn-sm btn-primary">
                            Edit
                        </a>
                    </div>
                @else
                        ({{ $member->name }})
                @endcan
            </li>
        @endforeach
    </ul>
@endif

@endsection
