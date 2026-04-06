@extends('layouts.app')

@section('content')
<div class="container">

<!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-4">Listings</h2>

        <a href="{{ route('listings.create') }}" class="btn btn-primary">
            New Listing
        </a>
    </div>


    {{-- Listing Selector --}}
    <form method="GET" action="{{ route('listings.index') }}" class="card mb-4">    
        <div class="card-body">
            <div class="row g-3">

                {{-- Listing Dropdown --}}
                <div class="col-12 col-md-4">
                    <label class="form-label">Listing</label>
                    <select name="listing_id" class="form-select" onchange="this.form.submit()">
                        @foreach($listings as $list)
                            <option value="{{ $list->id }}" @selected($list->id == $selectedListingId)>
                                {{ $list->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($listing)
                    {{-- From Date --}}
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label">From</label>
                        <input type="text"
                               class="form-control"
                               value="{{ optional($listing->date_from)->format('d.m.Y') }}"
                               readonly>
                    </div>

                    {{-- To Date --}}
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label">To</label>
                        <input type="text"
                               class="form-control"
                               value="{{ optional($listing->date_to)->format('d.m.Y') }}"
                               readonly>
                    </div>

                    {{-- Balance --}}
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label">Balance</label>
                        <input type="text"
                               class="form-control"
                               value="{{ number_format($balance, 2) }}"
                               readonly>
                    </div>

                    {{-- Edit Button --}}
                    <div class="col-12 col-sm-6 col-md-2 d-flex align-items-end ms-auto justify-content-end">
                        <a href="{{ route('listings.edit', $listing->id) }}"
                           class="btn btn-primary">
                            Edit
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </form>

    {{-- Grid --}}
    @if($selectedListingId)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th>Document</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $row)
                        <tr>
                            <td>
                                {{ $row->title }}
                                <div class="text-muted small">
                                    {{ $row->posting_date->format('d.m.Y') }}
                                    @if($row->user_id !== auth()->id())
                                        <span class="badge bg-secondary">
                                            {{ $row->user_name }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end" style="width: 1%; white-space: nowrap;">
                                {{ number_format($row->total_amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
