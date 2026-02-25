<div class="rollup-node mb-2">

        {{-- Node header --}}
    <li class="list-group-item d-flex align-items-center">
        <!-- LEFT: Name -->
        <div class="flex-grow-1">
            <span class="fw-bold">{{ $node->name }}</span>
        </div>

        <!-- MIDDLE: Code (left aligned, fixed width so all codes align vertically) -->
        <div class="flex-grow-0 text-center" style="width: 50px;">
            <span class="text-muted small">{{ $node->code }}</span>
        </div>

        <!-- RIGHT: Edit button -->
        <div class="ms-auto">
            <a href="{{ route('rollups.edit', ['rollup' => $node->id]) }}"
               class="btn btn-sm btn-primary">
               {{ $node->parent_id ? 'Edit' : 'Settings' }}
            </a>
        </div>
    </li>

    {{-- Children --}}
    @if ($node->children->count())
        <div class="ms-3 mt-2">
            @foreach ($node->children as $child)
                @include('rollups.partials.node', ['node' => $child])
            @endforeach
        </div>
    @endif

</div>
