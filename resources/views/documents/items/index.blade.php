@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h2>Items for: {{ $document->title }}</h2>

    <a href="{{ route('documents.items.create', $document) }}" class="btn btn-primary mb-3">
        Add Item
    </a>

    @if($items->isEmpty())
        <p class="text-muted">No items yet.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
