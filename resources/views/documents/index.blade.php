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

<!-- Month Selector -->
<div 
    x-data="monthSelector('{{ $currentMonth ?? now()->format('Y-m') }}')"
    x-init="init()" 
    class="d-flex align-items-center gap-3 mb-4 user-select-none">

    <button @click="prevMonth()" class="btn btn-outline-secondary px-3 py-1">←</button>

    <div 
        @click="openPicker = !openPicker" 
        class="fw-semibold cursor-pointer"
    >
        <span x-text="formatted"></span>
    </div>

    <button @click="nextMonth()" class="btn btn-outline-secondary px-3 py-1">→</button>

    <!-- Month Picker Dropdown -->
    <div 
        x-show="openPicker" 
        @click.outside="openPicker = false"
        class="absolute bg-white shadow rounded p-3 mt-10"
    >
        <template x-for="year in years">
            <div class="font-semibold mt-2" x-text="year"></div>
            <template x-for="m in 12">
                <div 
                    class="cursor-pointer hover:bg-gray-100 px-2 py-1"
                    @click="select(year, m)"
                    x-text="monthName(m)"
                ></div>
            </template>
        </template>
    </div>
</div>

<!-- Documents Table -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Document</th>
            <th>Date</th>
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

                <td class="cursor-pointer"
                    data-bs-toggle="collapse"
                    data-bs-target="#doc-{{ $doc->id }}">
                    {{ $doc->posting_date }}
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

<script>
function monthSelector(initial) {
    return {
        current: initial,
        openPicker: false,

        init() {
            document.addEventListener('keydown', (e) => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                if (document.querySelector('.modal.show')) return;

                if (e.key === 'ArrowLeft') this.prevMonth();
                if (e.key === 'ArrowRight') this.nextMonth();
                if (e.key === 'Home') this.goToCurrent();
            });
        },

        get formatted() {
            const [y, m] = this.current.split('-');
            return new Date(y, m - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
        },

        years: Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - 2 + i),

        monthName(m) {
            return new Date(2024, m - 1).toLocaleString('default', { month: 'long' });
        },

        select(year, month) {
            this.current = `${year}-${String(month).padStart(2, '0')}`;
            this.openPicker = false;
            this.navigate();
        },

        prevMonth() {
            const d = new Date(this.current + '-01');
            d.setMonth(d.getMonth() - 1);
            this.current = d.toISOString().slice(0, 7);
            this.navigate();
        },

        nextMonth() {
            const d = new Date(this.current + '-01');
            d.setMonth(d.getMonth() + 1);
            this.current = d.toISOString().slice(0, 7);
            this.navigate();
        },

        goToCurrent() {
            this.current = new Date().toISOString().slice(0, 7);
            this.navigate();
        },

        navigate() {
            const url = new URL(window.location.href);
            url.searchParams.set('month', this.current);
            window.location.href = url.toString();
        }
    }
}
</script>

@endsection
