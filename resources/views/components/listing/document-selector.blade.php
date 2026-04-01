<div class="document-selector">

    <p class="text-muted mb-3">
        Select the documents you want to attach to this listing.
    </p>

    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th style="width: 40px;"></th>
                <th>Document</th>
                <th>Date</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($documents as $doc)
                <tr>
                    <td>
                        <input 
                            type="checkbox" 
                            name="documents[]" 
                            value="{{ $doc->id }}"
                            @checked($listing->documents->contains($doc->id))
                        >
                    </td>

                    <td>{{ $doc->title }}</td>
                    <td>{{ $doc->posting_date->format('d-m-Y') }}</td>
                    <td class="text-end">{{ number_format($doc->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
