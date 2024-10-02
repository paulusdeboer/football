@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Games') }}</li>
            </ol>
        </div>
        <button type="submit" form="game-form" class="btn btn-primary mb-3">{{ __('Save Result') }}</button>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Enter Game Result') }}</div>

        <div class="card-body">
            <form method="POST" id="game-form" action="{{ route('games.store-result', $game) }}">
                @csrf

                <h5>{{ __('Players in Team 1') }}</h5>
                <ul>
                    @foreach ($game->teams()->where('team', 'team1')->get() as $player)
                        <li>{{ $player->name }}</li>
                    @endforeach
                </ul>

                <h5>{{ __('Players in Team 2') }}</h5>
                <ul>
                    @foreach ($game->teams()->where('team', 'team2')->get() as $player)
                        <li>{{ $player->name }}</li>
                    @endforeach
                </ul>

                <div class="form-group">
                    <label for="team1_score">{{ __('Team 1 Score') }}</label>
                    <input type="number" id="team1_score" name="team1_score" class="form-control mb-3" required>
                </div>

                <div class="form-group">
                    <label for="team2_score">{{ __('Team 2 Score') }}</label>
                    <input type="number" id="team2_score" name="team2_score" class="form-control mb-3" required>
                </div>
            </form>
        </div>
    </div>
@endsection
