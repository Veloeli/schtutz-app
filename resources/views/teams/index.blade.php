@extends('layouts.app')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Teams</h2>

    <a href="{{ route('teams.create') }}" class="btn btn-primary">
        New Team
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
                        The team can have common
                        @if($membership->team->has_common_financials)
                            • <strong>Financials</strong> 
                        @endif

                        @if($membership->team->has_common_reporting)
                            • <strong>Reporting</strong> 
                        @endif

                        @if($membership->team->has_common_securities)
                            • <strong>Securities</strong> 
                        @endif

                        @if($membership->team->has_common_financials)
                            <br>
                            Your private financials are <strong>{{ $membership->reveal_private ? 'visible' : 'not visible' }}</strong> to other team members
                            • You pick up <strong>{{ $membership->sharing_ratio + 0 }} of {{ $membership->team->sharing_ratio_sum + 0}}</strong> shares of each team category
                            @if($membership->clearingAccount)
                                • Your clearing account is <strong>{{ $membership->clearingAccount->name }}</strong>
                            @endif
                        @endif

                        @if(!(($membership->team->has_common_financials) || ($membership->team->has_common_reporting) || ($membership->team->has_common_securities)))
                            • <strong>No capabilities activated</strong>
                        @endif
                        @if($membership->team->valid_from || $membership->team->valid_to)
                        <br>
                            The team exists
                        @endif
                        @if($membership->team->valid_from)
                            from <strong>{{ $membership->team->valid_from->format('d.m.Y') }}</strong> 
                        @endif
                        @if($membership->team->valid_until)
                            until <strong>{{ $membership->team->valid_until->format('d.m.Y') }}</strong> 
                        @endif
                        @if($membership->member_from || $membership->member_to)
                        <br>
                            Your membership is active
                        @endif
                        @if($membership->member_from)
                            from <strong>{{ $membership->member_from->format('d.m.Y') }}</strong> 
                        @endif
                        @if($membership->member_to)
                            until <strong>{{ $membership->member_to->format('d.m.Y') }}</strong> 
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
                @endcan
            </li>
        @endforeach
    </ul>
@endif

@endsection
