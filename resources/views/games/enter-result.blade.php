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
        <div class="card-header">{{ __('Enter game result') }}</div>

        <div class="card-body">
            <form method="POST" id="game-form" action="{{ route('games.store-result', $game) }}">
                @csrf

                <div class="form-check mb-3">
                    <label class="form-check-label" for="send_rating_requests">{{ __('Send rating request e-mails') }}</label>
                    <input type="checkbox" id="send_rating_requests" name="send_rating_requests" class="form-check-input" @if ($game->team1_score) disabled @endif>
                </div>

                <div class="row">
                    <div class="col-3">
                        <h5>{{ __('Players in team 1') }}</h5>
                        <ul>
                            @foreach ($game->teams()->where('team', 'team1')->get() as $player)
                                <li>{{ $player->name }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="col-3">
                        <h5>{{ __('Players in team 2') }}</h5>
                        <ul>
                            @foreach ($game->teams()->where('team', 'team2')->get() as $player)
                                <li>{{ $player->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3 form-group">
                        <label for="team1_score">{{ __('Team 1 score') }}</label>
                        <input @if ($game->team1_score) value="{{ $game->team1_score }}" @endif type="number" id="team1_score" name="team1_score" class="form-control" required>
                    </div>

                    <div class="col-3 form-group">
                        <label for="team2_score">{{ __('Team 2 score') }}</label>
                        <input @if ($game->team2_score) value="{{ $game->team2_score }}" @endif type="number" id="team2_score" name="team2_score" class="form-control" required>
                    </div>
                </div>
            </form>
            <button type="submit" form="game-form" class="btn btn-primary mt-3">{{ __('Save result') }}</button>
        </div>
    </div>
    <a href="{{ route('games.index') }}" class="btn btn-secondary">{{ __('Back to games list') }}</a>
@endsection
