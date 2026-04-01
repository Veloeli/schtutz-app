@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- ========================= --}}
    {{-- 1. HEADER + ACTION BUTTONS --}}
    {{-- ========================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Listing: {{ $listing->name }}</h2>

        <div>
            <a href="{{ route('listings.index') }}" class="btn btn-secondary">Back</a>
            <button form="listing-form" class="btn btn-primary">Save Changes</button>
        </div>
    </div>


    {{-- ========================= --}}
    {{-- 2. LISTING DETAILS FORM   --}}
    {{-- ========================= --}}
    <form id="listing-form" method="POST" action="{{ route('listings.update', $listing) }}">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header fw-bold">
                Listing Details
            </div>

            <div class="card-body row g-3">

                <div class="col-md-12 mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $listing->name) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ old('date_from', $listing->date_from?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control"
                           value="{{ old('date_to', $listing->date_to?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Team</label>
                    <select name="team_id" class="form-select">
                        <option value="">— No team (private) —</option>

                        @foreach($teams as $team)
                            <option value="{{ $team->id }}"
                                @selected(old('team_id', $listing->team_id) == $team->id)>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- ===================================== --}}
        {{-- 4. ITEM SELECTION (GROUPED BY DOC)    --}}
        {{-- ===================================== --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Select Items</span>

                <button class="btn btn-sm btn-outline-secondary" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#items-section">
                    Toggle
                </button>
            </div>

            <div id="items-section" class="collapse show">
                <div class="card-body">
                    {{-- Reusable component --}}
                    <x-listing.item-selector 
                        :listing="$listing"
                        :documents="$documents"
                    />
                </div>
            </div>
        </div>
    </form>

</div>

@endsection

