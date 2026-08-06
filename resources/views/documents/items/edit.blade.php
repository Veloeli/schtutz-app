@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h2 class="mb-4">Edit Item in Document: "{{ $document->title }}"</h2>

    <form method="POST" action="{{ route('documents.items.update', [$document, $item]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Item Name</label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $item->name) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">
                Amount
                @if($document->currency_id)
                    <span class="text-muted">
                        (in {{ $document->currency_name }})
                    </span>
                @endif
            </label>
            <input type="number" step="0.000001" name="amount"
                   autocomplete="off"
                   class="form-control"
                   value="{{ old('amount', formatAmountNumeric($item->amount ?? '')) }}"
        </div>

        @php
            $current = $item->category ?? null;

            // Check if the current category is NOT in the selectable list
            $isCurrentMissing = $current && ! $categories->contains('id', $current->id);
        @endphp

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>

                {{-- If the current category is missing, show it as a special option --}}
                @if ($isCurrentMissing)
                    <option value="{{ $current->id }}" selected data-archived="true">
                        {{ $current->code }} {{ $current->name }}
                        @if ($current->source_label)
                            [{{ $current->source_label }}]
                        @endif
                        (no longer selectable)
                    </option>
                    <option disabled>──────────</option>
                @else
                    <option value="">-- Select Category --</option>
                @endif

                {{-- Active/selectable categories --}}
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $item->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->path_to_category }}
                        @if ($category->source_label)
                            [{{ $category->source_label }}]
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" step="0.001" name="quantity"
                   class="form-control"
                   value="{{ old('quantity', formatQuantityNumeric($item->quantity ?? '')) }}"
        </div>

        <div class="d-flex justify-content-between mt-4">

            <!-- Left side: Update + Cancel -->
            <div class="d-flex gap-2">
                @can('update', $item)
                <button type="submit" class="btn btn-primary">Save Changes</button>
                @endcan
                
                <a href="{{ route('documents.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

            <!-- Right side: Delete (opens modal) -->
            @can('delete', $item)
            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal">
                Delete
            </button>
            @endcan

        </div>

    </form>
</div>

<!-- DELETE ITEM MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Delete item <strong id="itemName"></strong>?</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                @can('delete', $item)
                <form id="deleteItemForm" 
                    method="POST"
                    action="{{ route('documents.items.destroy', [$document, $item]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.querySelector('input[name="name"]');
    const categorySelect = document.querySelector('select[name="category_id"]');

    let timer = null;

    nameInput.addEventListener('input', function () {
        clearTimeout(timer);

        const value = this.value.trim();
        if (value.length < 3) return;

        timer = setTimeout(() => {
            fetch(`{{ route('documents.items.suggest-category', $document) }}?name=${encodeURIComponent(value)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.category_id) {
                        categorySelect.value = data.category_id;
                    }
                });
        }, 300);
    });
});
</script>

@endsection
