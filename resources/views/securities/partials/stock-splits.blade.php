{{-- resources/views/securities/partials/stock_splits.blade.php --}}

<div class="col-12">
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Stock Splits</span>

            <a href="{{ route('stock-splits.create', ['old_id' => $security->id]) }}"
               class="btn btn-sm btn-primary">
                New
            </a>
        </div>

            @php
                $splits = $security->splitsFrom
                    ->merge($security->splitsTo)
                    ->sortBy('split_date');
            @endphp

            @if($splits->isNotEmpty())
                <table class="table table-hover">
                    <tbody>
                        @foreach($splits as $split)
                            <tr>
                                <td>
                                    {!! $split->oldSecurity->linkUnless($security) !!}
                                    ⟶
                                    {!! $split->newSecurity->linkUnless($security) !!}
                                    <div class="text-muted small">
                                        {{ $split->split_date->format('d-m-Y') }}
                                        {{ $split->direction === 'forward' ? 'Forward' : 'Reverse' }}
                                        {{ formatQuantity($split->split_factor) }}
                                    </div>
                                </td>

                                <td style="width: 1%; white-space: nowrap;">
                                    <a href="{{ route('stock-splits.edit', $split) }}" class="btn btn-sm btn-primary">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted ms-3 mt-2">
                    No stock splits recorded for this security.
                </p>
            @endif
        </div>
    </div>
</div>
