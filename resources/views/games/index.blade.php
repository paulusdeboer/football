@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Games List') }}</div>

                    <div class="card-body">
                        <a href="{{ route('games.create') }}" class="btn btn-primary mb-3">{{ __('Create New Game') }}</a>

                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{ __('Game Date') }}</th>
                                <th>{{ __('Team 1 Score') }}</th>
                                <th>{{ __('Team 2 Score') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($games as $game)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($game->played_at)->format('d-m-Y') }}</td>
                                    <td>{{ $game->team1_score }}</td>
                                    <td>{{ $game->team2_score }}</td>
                                    <td>
                                        <a href="{{ route('games.show', $game) }}" class="btn btn-info btn-sm">{{ __('View') }}</a>
                                        @if ($game->team1_score === null && $game->team2_score === null)
                                            <a href="{{ route('games.edit', $game) }}" class="btn btn-warning btn-sm">{{ __('Edit Game') }}</a>
                                            <a href="{{ route('games.enter-result', $game) }}" class="btn btn-warning btn-sm">{{ __('Enter Result') }}</a>
                                        @else
                                            <a href="{{ route('games.enter-result', $game) }}" class="btn btn-warning btn-sm">{{ __('Edit Result') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
