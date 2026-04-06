@extends('layouts.app')

@section('content')
<div class="container">

<!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-4">Stock Quotes</h2>

        <a href="{{ route('quotes.import') }}" class="btn btn-primary">
            Import Quotes
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('quotes.index') }}" class="card mb-4">
        <div class="card-body">
            <div class="row g-3">

                {{-- Security Filter --}}
                <div class="col-12 col-md-4">
                    <label class="form-label">Security</label>
                    <select name="security_id" class="form-select">
                        <option value="">All</option>
                        @foreach($securities as $security)
                            <option value="{{ $security->id }}"
                                @selected(request('security_id') == $security->id)>
                                {{ $security->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- From Date --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date"
                           value="{{ request('from_date') }}"
                           class="form-control">
                </div>

                {{-- To Date --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date"
                           value="{{ request('to_date') }}"
                           class="form-control">
                </div>

                {{-- Submit --}}
                <div class="col-12 col-md-2 d-grid">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-primary">
                        Filter
                    </button>
                </div>

            </div>
        </div>
    </form>

    {{-- Quotes Table --}}
    @if($quotes->isEmpty())
        <p class="text-muted">
            No quotes yet.
        </p>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Security</th>
                        <th class="text-end">Price</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($quotes as $quote)
                        <tr>
                            <td class="text-wrap" style="white-space: normal;">
                                {{ $quote->security?->name }}
                                <div class="text-muted small">
                                    {{ $quote->quote_date->format('d-m-Y') }}
                                </div>
                            </td>

                            <td class="text-end" style="width: 1%; white-space: nowrap;">
                                {{ number_format($quote->price, 4) }}
                                <div class="text-muted small">
                                    {{ $quote->security?->currency?->name ?? '' }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-sm >
            {{ $quotes->appends(request()->query())->onEachSide(0)->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
