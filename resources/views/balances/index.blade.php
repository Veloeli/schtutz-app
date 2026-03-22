@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-4">Balances</h2>
    </div>

    <form method="GET" action="{{ route('balances.index') }}" class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        <!-- Root Selector -->
        <x-root-selector action="{{ route('balances.index') }}" />

        <!-- User/Team Selector -->
        <x-user-team-selector
            :teamfilter="$teamfilter"
        />

        <!-- Date Selector -->
        <div class="w-auto">
            <input 
                type="date" 
                name="date"
                value="{{ request('date', now()->toDateString()) }}"
                class="form-control"
                onchange="this.form.submit()"
            />
        </div>
    </form>

    {{-- Balances table --}}
    @if($balances->isEmpty())
        <p class="text-muted">
            No balances with these filter conditions.
        </p>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($balances as $row)
                        <tr>
                            <td style="padding-left: {{ $row->depth * 20 }}px;">
                                {{ $row->name }}
                                @if($row->team_id)
                                    <span class="badge bg-secondary">
                                        {{ $row->team->name }}
                                    </span>
                                @elseif($row->category_id && $row->category->owner->id !== auth()->id()))
                                    <span class="badge bg-secondary">
                                        {{ $row->category->owner->name}}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                {{ number_format($row->total_amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
