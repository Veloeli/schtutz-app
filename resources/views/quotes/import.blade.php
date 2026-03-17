@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="h3 mb-4">
        <i class="bi bi-cash-coin me-2"></i>
        Import Quotes
    </h1>

    <form method="POST" action="{{ route('quotes.import.process') }}">
        @csrf

        <div class="card mb-4">
            <div class="card-body">

                {{-- Preselect Security --}}
                <div class="mb-3">
                    <label class="form-label">Security (optional)</label>
                    <select name="security_id" class="form-select">
                        <option value="">— Detect from first column —</option>
                        @foreach($securities as $security)
                            <option value="{{ $security->id }}">{{ $security->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Preselect Date --}}
                <div class="mb-3">
                    <label class="form-label">Date (optional)</label>
                    <input type="date" name="fixed_date" class="form-control">
                </div>

                {{-- Paste Area --}}
                <div class="mb-3">
                    <label class="form-label">Paste Spreadsheet Data</label>
                    <textarea name="raw" rows="10" class="form-control"
                        placeholder="Paste rows from Excel or Google Sheets here..."></textarea>
                </div>

                <button class="btn btn-primary">
                    Analyze & Import
                </button>

            </div>
        </div>

    </form>

</div>
@endsection
