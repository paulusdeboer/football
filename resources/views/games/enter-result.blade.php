@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Enter Game Result') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('games.store-result', $game) }}">
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
                                <input type="number" id="team1_score" name="team1_score" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="team2_score">{{ __('Team 2 Score') }}</label>
                                <input type="number" id="team2_score" name="team2_score" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('Save Result') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
