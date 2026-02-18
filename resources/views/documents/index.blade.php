@extends('layouts.app')

@section('content')

@php
    $expandedDocs = session('expanded_docs', []);
@endphp

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-4">Documents</h2>

    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Document
    </a>
</div>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <!-- Month Selector -->
    <x-month-picker name="month" :value="$month" />

    <form method="GET" class="mb-3">
        <select name="filter" class="form-select w-auto d-inline-block" onchange="this.form.submit()">

            <option value="">All</option>

            <optgroup label="Teams">
                @foreach ($teams as $team)
                    <option value="team-{{ $team->id }}"
                        {{ request('filter') === 'team-'.$team->id ? 'selected' : '' }}>
                        {{ $team->name }}
                    </option>
                @endforeach
            </optgroup>

            <optgroup label="Members">
                @foreach ($members as $member)
                    <option value="member-{{ $member->id }}"
                        {{ request('filter') === 'member-'.$member->id ? 'selected' : '' }}>
                        {{ $member->name }}
                    </option>
                @endforeach
            </optgroup>

        </select>
    </form>
</div>


<!-- Documents Table -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Document</th>
            <th>Actions</th>
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
                        {{ $doc->posting_date }}

                        @if($doc->owner_id !== auth()->id())
                            <span class="badge bg-secondary">
                                {{ $doc->owner->name }}
                            </span>
                        @endif
                    </div>
                </td>

                <td style="width: 1%; white-space: nowrap;">
                    @can('update', $doc)
                    <a href="{{ route('documents.edit', $doc) }}"
                       class="btn btn-sm btn-primary">
                        Edit
                    </a>
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
                                                                {{ $item->quantity * 1 }} ×
                                                            @endif
                                                            {{ $item->name }}
                                                        </div>

                                                        <div class="text-muted small">
                                                            <span class="badge bg-secondary">
                                                                @if($item->category->team_id)
                                                                    {{ $item->category->team->name }}
                                                                @else
                                                                    {{ $doc->owner->name }}
                                                                @endif
                                                            </span>
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
                                                           class="btn btn-sm btn-primary">
                                                            Edit
                                                        </a>
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
                                    Add Item
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
