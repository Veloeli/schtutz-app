@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Create Document</h2>

    <br>
    <form method="POST" action="{{ route('documents.store') }}">
        @csrf

        {{-- TITLE --}}
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text"
                   name="title"
                   class="form-control"
                   required>
        </div>

        {{-- META ROW --}}
        <div class="row">

            {{-- Posting Date --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Posting Date</label>
                <input type="date"
                       name="posting_date"
                       class="form-control"
                       value="{{ $posting_date }}"
                       required>
            </div>

            {{-- Currency --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Currency</label>
                <select name="currency_id" class="form-select">
                    <option value="">No currency</option>

                    @foreach($availableCurrencies as $currency)
                        <option value="{{ $currency->id }}">
                            {{ $currency->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Document Owner --}}
            <div class="col-md-6 mb-3">
                <label class="form-label">Document Owner</label>
                <select name="user_id" class="form-select">
                    @foreach($possibleOwners as $owner)
                        <option value="{{ $owner->id }}"
                            @selected($owner->id === auth()->id())>
                            {{ $owner->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
        
        <br>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('documents.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
