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

<!-- Documents Table -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Document</th>
            <th width="120">Actions</th>
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
                </td>

                <td>
                    <a href="{{ route('documents.edit', $doc) }}"
                       class="btn btn-sm btn-outline-primary">
                        Edit
                    </a>
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
                                            <li class="list-group-item d-flex justify-content-between align-items-center">

                                                <div>
                                                    <strong>
                                                        @if($item->quantity !== null)
                                                            {{ $item->quantity * 1 }}
                                                        @endif
                                                        {{ $item->name }}
                                                        @if($item->amount !== null)
                                                            {{ number_format($item->amount, 2) }}
                                                        @endif
                                                    </strong>

                                                    <div class="text-muted small">
                                                        @if($item->category)
                                                            {{ $item->category->name }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('documents.items.edit', [$doc, $item]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        Edit
                                                    </a>
                                                </div>

                                            </li>
<!--                                            <li class="list-group-item d-flex justify-content-between align-items-center">

                                                <div>
                                                    <strong>{{ $item->name }}</strong>

                                                    <div class="text-muted small">
                                                        @if($item->quantity !== null)
                                                            Qty: {{ number_format($item->quantity, 3) }}
                                                        @endif

                                                        @if($item->amount !== null)
                                                            • Amount: {{ number_format($item->amount, 2) }}
                                                        @endif

                                                        @if($item->category)
                                                            • Category: {{ $item->category->name }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('documents.items.edit', [$doc, $item]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        Edit
                                                    </a>
                                                </div>

                                            </li> -->
                                        @endforeach
                                    </ul>
                                @endif

                                <a href="{{ route('documents.items.create', $doc) }}"
                                   class="btn btn-primary btn-sm">
                                    Add Item
                                </a>

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
