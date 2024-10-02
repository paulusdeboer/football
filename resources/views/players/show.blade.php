@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Games') }}</li>
            </ol>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Game Details') }}</div>

        <div class="card-body">
            <p><strong>{{ __('Date') }}:</strong> {{ Carbon::parse($game->played_at)->format('d-m-Y') }}</p>
            <p><strong>{{ __('Result') . ': ' }}</strong> {{ $game->team1_score ? $game->team1_score . ' - ' . $game->team2_score : ''}}</p>

            <h5>{{ __('Players in Team 1') . ' (' . $team1Rating . ')'}}</h5>
            <ul>
                @foreach ($game->teams()->where('team', 'team1')->get()->sortByDesc('rating') as $player)
                    <li>{{ '(' . ($player->type === 'attacker' ? 'A' : ($player->type === 'defender' ? 'D' : 'B')) . ') ' . $player->name }}</li>
                @endforeach
            </ul>

            <h5>{{ __('Players in Team 2') . ' (' . $team2Rating . ')'}}</h5>
            <ul>
                @foreach ($game->teams()->where('team', 'team2')->get()->sortByDesc('rating') as $player)
                    <li>{{ '(' . ($player->type === 'attacker' ? 'A' : ($player->type === 'defender' ? 'D' : 'B')) . ') ' . $player->name }}</li>
                @endforeach
            </ul>

            <a href="{{ route('games.index') }}" class="btn btn-secondary">{{ __('Back to Games List') }}</a>
        </div>
    </div>
@endsection

