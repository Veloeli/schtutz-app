<div class="item-selector">

    <p class="text-muted mb-3">
        Select the individual items you want to include in this listing.
    </p>

    @foreach ($documents as $doc)
        <div class="mb-4 border rounded p-3">

            <h6 class="fw-bold mb-2">
                {{ $doc->title }}
                <span class="text-muted">({{ $doc->posting_date->format('d-m-Y') }})</span>
            </h6>

            @if ($doc->items->isEmpty())
                <p class="text-muted fst-italic">No items found for this document.</p>
            @else
                <table class="table table-sm align-middle">
                    <thead>     
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Sign</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($doc->items as $item)
                        <tr>
                            <td>
                                <input 
                                    type="checkbox" 
                                    name="items[]" 
                                    value="{{ $item->id }}"
                                    @checked($listing->items->contains($item->id))
                                >
                            </td>

                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category->type_label }} - {{ $item->category->name }}</td>
                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>

                            <td class="text-center" style="width: 80px;">
                                <input 
                                    type="checkbox"
                                    name="change_sign[{{ $item->id }}]"
                                    value="1"
                                    @checked(
                                        optional($listing->items->firstWhere('id', $item->id))->pivot->change_sign ?? false
                                    )
                                >
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    @endforeach

</div>
