@extends('layouts.app')

@section('content')
<div class="container-py4">
    <h2>Edit Listing: {{ $listing->name }}</h2>

    {{--   LISTING DETAILS FORM   --}}
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

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="d-flex gap-2">
                <!-- Save -->
                <button type="submit" class="btn btn-primary">Save Changes</button>

                <a href="{{ route('listings.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

            <!-- Right side: Delete (opens modal) -->
            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal">
                Delete
            </button>
        </div>
        <br>

        {{--   ITEM SELECTION (GROUPED BY DOC)    --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Select Items</span>

            </div>

            <div id="items-section">
                <div class="card-body">
                    {{-- Reusable component --}}
                    <p class="text-muted mb-3">
                        Select the individual items you want to include in this listing.
                    </p>

                    @foreach ($documents as $doc)
                        <div class="mb-4 border rounded p-3">

                            <h6 class="fw-bold mb-2">
                                {{ $doc->title }}
                                <span class="text-muted">({{ $doc->posting_date->format('d.m.Y') }})</span>
                                    @if($doc->user_id !== auth()->id())
                                        <span class="badge bg-secondary">
                                            {{ $doc->user->name }}
                                        </span>
                                    @endif
                            </h6>

                            @if ($doc->items->isEmpty())
                                <p class="text-muted fst-italic">No items found for this document.</p>
                            @else
                                <table class="table table-hover">
                                    <thead>
                                        <tr class="bg-gray-100 text-left">
                                            <th style="width: 30px;"></th>
                                            <th>Item</th>
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

                                            <td>
                                                {{ $item->name }}
                                                <div class="text-muted small">
                                                    {{ $item->category->type_label }} - {{ $item->category->name }}
                                                    @if($item->category->team_id)
                                                        <span class="badge bg-secondary">
                                                            {{ $item->category->team->name }}
                                                        </span>
                                                    @elseif($doc->user_id !== auth()->id())
                                                        <span class="badge bg-secondary">
                                                            {{ $doc->user->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>

                                            <td class="text-center" style="width: 60px;">
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
                    {{ $documents->links() }}

                </div>
            </div>
        </div>

        {{--   ORIGINAL ITEM SELECTION (SO THE CONTROLLER CAN COMPARE FOR CHANGES AND SAVE ACCORDINGLY LATER)  --}}
        @foreach ($originalItems as $id)
            <input type="hidden" name="original_items[]" value="{{ $id }}">
        @endforeach

    </form>

</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Delete Listing</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $listing->name }}</strong>?</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <form id="deleteCategoryForm"
                      method="POST"
                      action="{{ route('listings.destroy', $listing) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete Listing</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

