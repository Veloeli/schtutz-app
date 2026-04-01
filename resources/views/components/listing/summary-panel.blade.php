<div class="summary-panel">

    <p class="text-muted mb-3">
        Overview of the selected documents and items.
    </p>

    <ul class="list-group">

        <li class="list-group-item d-flex justify-content-between">
            <span>Attached Documents</span>
            <strong>{{ $listing->documents->count() }}</strong>
        </li>

        <li class="list-group-item d-flex justify-content-between">
            <span>Selected Items</span>
            <strong>{{ $listing->items->count() }}</strong>
        </li>

        <li class="list-group-item d-flex justify-content-between">
            <span>Total Amount</span>
            <strong>
                {{ number_format($listing->items->sum('amount'), 2) }}
            </strong>
        </li>

    </ul>

</div>
