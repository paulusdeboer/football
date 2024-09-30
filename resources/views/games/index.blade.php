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
        <a href="{{ route('games.create') }}" class="btn btn-primary mb-3">{{ __('Create New Game') }}</a>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            {{ __('Games List') }}
        </div>

        <div class="card-body">

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
                        <td>{{ Carbon::parse($game->played_at)->format('d-m-Y') }}</td>
                        <td>{{ $game->team1_score }}</td>
                        <td>{{ $game->team2_score }}</td>
                        <td>
                            <a href="{{ route('games.show', $game) }}"
                               class="btn btn-info btn-sm">{{ __('View') }}</a>
                            @if ($game->team1_score === null && $game->team2_score === null)
                                <a href="{{ route('games.edit', $game) }}"
                                   class="btn btn-warning btn-sm">{{ __('Edit Game') }}</a>
                                <a href="{{ route('games.enter-result', $game) }}"
                                   class="btn btn-warning btn-sm">{{ __('Enter Result') }}</a>
                            @else
                                <a href="{{ route('games.enter-result', $game) }}"
                                   class="btn btn-warning btn-sm">{{ __('Edit Result') }}</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
