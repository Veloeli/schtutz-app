@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h2 class="mb-4">Create Item in Document: {{ $document->title }}</h2>

    <form method="POST" action="{{ route('documents.items.store', $document) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Item Name</label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $item->name ?? '') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" step="0.01" name="amount"
                   class="form-control"
                   value="{{ old('amount', $item->amount ?? '') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $item->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->path_to_category }}
                        @if ($category->source_label)
                            ⟦{{ $category->source_label }}⟧
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" step="0.001" name="quantity"
                   class="form-control"
                   value="{{ old('quantity', $item->quantity ?? '') }}">
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ route('documents.index', $document) }}" class="btn btn-secondary">Cancel</a>
    </form>
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
