@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Discover Categories</h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Owner</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->owner->name }}</td>
                    <td style="width: 1%; white-space: nowrap;">
                        <form action="{{ route('categories.subscribe', $category) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                {{ $category->isSubscribedBy(auth()->user()) ? 'Unsubscribe' : 'Subscribe' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
