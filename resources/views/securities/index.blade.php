@extends('layouts.app')

@section('content')
<div class="container">

<!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-4">Securities</h2>

        <a href="{{ route('securities.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New
        </a>
    </div>

@if($securities->isEmpty())
    <p class="text-muted">
        No securities yet.
    </p>
@else
<!-- Search field -->
    <form method="GET" action="{{ route('securities.index') }}" class="mb-3">
        <div class="input-group">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search name, ticker, ISIN, identifier…" 
                value="{{ request('search') }}"
            >
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>
    
<!-- Table -->
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Security</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($securities as $security)
                <tr>
                    <td>
                        @if(!$security->is_in_use)<del>@endif
                        {{ $security->name }}
                        @if(!$security->is_in_use)</del>@endif

                        @if($security->is_tracked)
                            <span class="text-warning" title="Tracked security">⭐</span>
                        @endif

                        <div class="text-muted small">
                            {{ $security->asset_class_label }}
                            
                            @if ($security->source_label)
                                <span class="badge bg-secondary">
                                    {{ $security->source_label }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td style="width: 1%; white-space: nowrap;">
                        <a href="{{ route('securities.edit', $security) }}" class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

    {{ $securities->links() }}

</div>
@endsection
