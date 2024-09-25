@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Game Details') }}</div>

                    <div class="card-body">
                        <p><strong>{{ __('Game Date') }}:</strong> {{ \Carbon\Carbon::parse($game->played_at)->format('d-m-Y') }}</p>
                        <p><strong>{{ __('Team 1 Score:') }}</strong> {{ $game->team1_score }}</p>
                        <p><strong>{{ __('Team 2 Score:') }}</strong> {{ $game->team2_score }}</p>

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

                        <a href="{{ route('games.index') }}" class="btn btn-secondary">{{ __('Back to Games List') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

