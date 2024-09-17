@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Rate Player ({{ $player->name }})</h1>
        <form method="POST" action="{{ route('players.rate.store', [$game->id, $player->id]) }}">
            @csrf
            <div class="form-group">
                <label for="rating">Rating</label>
                <input type="number" name="rating_value" class="form-control" min="5" max="10" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@endsection
