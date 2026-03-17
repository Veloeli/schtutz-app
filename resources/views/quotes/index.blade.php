@extends('layouts.app')

@section('content')
<div class="container">

<!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-4">Stock Quotes</h2>

        <a href="{{ route('quotes.import') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Import
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('quotes.index') }}" class="card mb-4">
        <div class="card-body">
            <div class="row g-3">

                {{-- Security Filter --}}
                <div class="col-md-4">
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
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date"
                           value="{{ request('from_date') }}"
                           class="form-control">
                </div>

                {{-- To Date --}}
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date"
                           value="{{ request('to_date') }}"
                           class="form-control">
                </div>

                {{-- Submit --}}
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        Filter
                    </button>
                </div>

            </div>
        </div>
    </form>

    {{-- Quotes Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Security</th>
                        <th>Date</th>
                        <th class="text-end">Price</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($quotes as $quote)
                        <tr>
                            <td>{{ $quote->security->name }}</td>
                            <td>{{ $quote->quote_date->format('Y-m-d') }}</td>
                            <td class="text-end">{{ number_format($quote->price, 4) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">
                                No quotes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer">
            {{ $quotes->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection
