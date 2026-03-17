@extends('layouts.app')

@section('content')

<h2 class="h3 mb-4">Import Quotes</h2>

<form method="POST" action="{{ route('quotes.import.process') }}">
    @csrf

    <div class="row">
        {{-- Preselect Date --}}
        <div class="col-md-4 mb-3">

            <label class="form-label">Date (optional)</label>
            <input type="date" name="fixed_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>

        {{-- Preselect Security --}}
        <div class="col-md-8 mb-3">
            <label class="form-label">Security (optional)</label>
            <select name="security_id" class="form-select">
                <option value="">— Detect from first column —</option>
                @foreach($securities as $security)
                    <option value="{{ $security->id }}">{{ $security->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Paste Area --}}
    <div class="mb-3">
        <label class="form-label">Paste Spreadsheet Data</label>
        <textarea name="raw" rows="10" class="form-control"
            placeholder="Paste rows from Excel or Google Sheets here..."></textarea>
    </div>

    <button class="btn btn-primary">
        Analyze and Import
    </button>

</form>

@endsection
