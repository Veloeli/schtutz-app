@extends('layouts.app')

@section('content')

@php
    $expandedDocs = session('expanded_docs', []);
@endphp

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Documents</h2>

    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        New Document
    </a>
</div>

<form method="GET" action="{{ route('documents.index') }}" class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <!-- Month Selector -->
    <x-month-selector
        name="month"
        :value="$month"
        :min-month="$minMonth"
        :max-month="$maxMonth"
    />

    <!-- User/Team Selector -->
    <x-user-team-selector
        :action="route('documents.index')"
        :teamfilter="$teamfilter"
    />
</form>


<!-- Documents Table -->
@if($documents->isEmpty())
    <p class="text-muted">
        No documents in this selection.
    </p>
@else
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Document</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach($documents as $doc)

                {{-- DOCUMENT ROW --}}
                <tr>
                    <td class="cursor-pointer"
                        data-bs-toggle="collapse"
                        data-bs-target="#doc-{{ $doc->id }}">

                        {{ $doc->title }} 
                        <div class="text-muted small">
                            {{ $doc->posting_date->format('d.m.Y') }}

                            @if($doc->user_id !== auth()->id())
                                <span class="badge bg-secondary">
                                    {{ $doc->user->name }}
                                </span>
                            @endif
                            @if (!isZeroAmount($doc->amount_sum))
                                <span class="badge bg-danger">
                                    Balance: {{ formatAmount($doc->amount_sum) }}
                                </span>
                            @endif
                        </div>
                    </td>

                    <td style="width: 1%; white-space: nowrap;">
                        @can('update', $doc)
                        <a href="{{ route('documents.edit', $doc) }}" 
                           id="edit-document-{{ $doc->id }}"
                           class="btn btn-sm btn-primary">
                            Edit
                        </a>
                        @else
                            <span id="view-document-{{ $doc->id }}" class="text-muted">
                                
                            </span>
                        @endcan
                    </td>
                </tr>

                {{-- COLLAPSIBLE CARD ROW --}}
                <tr>
                    <td colspan="2" class="p-0 border-0">

                        <div class="collapse {{ ($expandedDocs[$doc->id] ?? false) ? 'show' : '' }}"
                             id="doc-{{ $doc->id }}">

                            <div class="card bg-dark-subtle text-white border-0 rounded-0">
                                <div class="card-body">

                                    @if($doc->items->isEmpty())
                                        <p class="text-muted mb-0">No items yet.</p>
                                    @else
                                        <ul class="list-group mb-3">
                                            @foreach($doc->items as $item)
                                                <li class="list-group-item">
                                                    <div class="row align-items-center">

                                                        <!-- LEFT COLUMN: quantity + name + category -->
                                                        <div class="col-6">
                                                            <div class="fw-bold">
                                                                @if($item->quantity !== null)
                                                                    {{ formatQuantity($item->quantity) }} ×
                                                                @endif
                                                                {{ $item->name }}
                                                            </div>

                                                            <div class="text-muted small">
                                                                @if($item->category->team_id)
                                                                    <span class="badge bg-secondary">
                                                                        {{ $item->category->team->name }}
                                                                    </span>
                                                                @elseif($doc->user_id !== auth()->id())
                                                                    <span class="badge bg-secondary">
                                                                        {{ $doc->user->name }}
                                                                    </span>
                                                                @endif
                                                                {{ $item->category->name }}
                                                            </div>
                                                        </div>

                                                        <!-- MIDDLE COLUMN: amount -->
                                                        <div class="col-3 text-end">
                                                            @if($item->amount !== null)
                                                                <span class="fw-bold">
                                                                    {{ number_format($item->amount, 2) }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <!-- RIGHT COLUMN: actions -->
                                                        <div class="col-3 text-end">
                                                            @can('update', $item)
                                                            <a href="{{ route('documents.items.edit', [$doc, $item]) }}"
                                                               id="edit-item-{{ $item->id }}"
                                                               class="btn btn-sm btn-primary">
                                                                Edit
                                                            </a>
                                                            @else
                                                                <span id="view-item-{{ $item->id }}" class="text-muted">
                                                                    
                                                                </span>
                                                            @endcan
                                                        </div>

                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @can('update', $doc)
                                    <a href="{{ route('documents.items.create', $doc) }}"
                                       class="btn btn-primary btn-sm">
                                        New Item
                                    </a>
                                    @endcan

                                </div>
                            </div>

                        </div>

                    </td>
                </tr>

            @endforeach
        </tbody>
    </table>
@endif

@endsection

@section('scripts')
<script>
// store expanded/collapsed state of documents
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.collapse').forEach(collapse => {

        collapse.addEventListener('show.bs.collapse', function () {
            const id = this.id.replace('doc-', '');
            saveState(id, true);
        });

        collapse.addEventListener('hide.bs.collapse', function () {
            const id = this.id.replace('doc-', '');
            saveState(id, false);
        });

    });

    function saveState(id, expanded) {
        fetch("{{ route('documents.toggle') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id, expanded })
        });
    }
});

// scroll to expanded document
document.addEventListener('DOMContentLoaded', function () {
    const expanded = @json($expandedDocs);

    const firstExpandedId = Object.keys(expanded).find(id => expanded[id] === true);

    if (firstExpandedId) {
        const row = document.getElementById('doc-' + firstExpandedId);
        if (row) {
            row.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }
});

</script>

@endsection
