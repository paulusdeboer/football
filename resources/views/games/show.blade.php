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
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                {{ __('Game details') }}
            </div>
            <a href="{{ route('games.edit', $game) }}"
               class="btn btn-primary btn-sm">{{ __('Edit game') }}</a>
        </div>

        <div class="card-body">
            <p><strong>{{ __('Date') }}:</strong> {{ Carbon::parse($game->played_at)->format('d-m-Y') }}</p>
            <p><strong>{{ __('Result') . ': ' }}</strong> {{ $game->team1_score ? $game->team1_score . ' - ' . $game->team2_score : ''}}</p>
            <strong>{{ __('Rating requests have been sent to') . ': ' }}</strong>
            @if(!empty($ratingRequests))
                <ul>
                    @foreach($ratingRequests as $request)
                        <li>{{ $request->player->name }} ({{ $request->player->user->email }})</li>
                    @endforeach
                </ul>
            @endif

            <h5>{{ __('Players in team 1') . ' (' . $team1Rating . ')'}}</h5>
            <ul>
                @foreach ($team1Ratings->sortBy('player.name') as $rating)
                    <li class="player-rating">
                        {{ '(' . ($rating->type === 'attacker' ? 'A' : ($rating->type === 'defender' ? 'D' : 'B')) . ') ' . $rating->player->name }}
                        <span class="rating-value"> - {{ $rating->rating }}</span>
                    </li>
                @endforeach
            </ul>

            <h5>{{ __('Players in team 2') . ' (' . $team2Rating . ')'}}</h5>
            <ul>
                @foreach ($team2Ratings->sortBy('player.name') as $rating)
                    <li class="player-rating">
                        {{ '(' . ($rating->type === 'attacker' ? 'A' : ($rating->type === 'defender' ? 'D' : 'B')) . ') ' . $rating->player->name }}
                        <span class="rating-value"> - {{ $rating->rating }}</span>
                    </li>
                @endforeach
            </ul>

            <button id="toggleRatings" class="btn btn-sm btn-primary">{{ __('Hide ratings') }}</button>

        </div>
    </div>

    <a href="{{ route('games.index') }}" class="btn btn-secondary">{{ __('Back to games list') }}</a>

    {{-- JavaScript voor toggelen van ratings --}}
    <script>
        document.getElementById('toggleRatings').addEventListener('click', function () {
            let ratings = document.querySelectorAll('.rating-value');
            let isHidden = ratings[0].style.display === 'none';

            ratings.forEach(rating => {
                rating.style.display = isHidden ? 'inline' : 'none';
            });

            this.textContent = isHidden ? '{{ __('Hide ratings') }}' : '{{ __('Show ratings') }}';
        });
    </script>
@endsection

