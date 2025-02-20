@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Given ratings') }}</li>
            </ol>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            {{ __('Ratings list') }}
        </div>

        <div class="card-body">

            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Game date') }}</th>
                    <th>{{ __('Ratings') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($games as $game)
                    <tr>
                        <td>{{ Carbon::parse($game->played_at)->format('d-m-Y') }}</td>
                        <td>
                            @if ($game->ratings->isEmpty())
                                <span class="text-muted">{{ __('No ratings given') }}</span>
                            @else
                                @php
                                    $players = $game->teams->unique();
                                    $ratingsByPlayer = $game->ratings->groupBy('rating_player_id');
                                @endphp

                                <div class="d-flex flex-wrap gap-5">
                                    @foreach ($ratingsByPlayer as $ratingPlayerId => $ratings)
                                        <div>
                                            <strong>{{ $ratings->first()->ratingPlayer->name }}</strong>
                                            <table class="table table-sm mb-0">
                                                @foreach ($players as $ratedPlayer)
                                                    @php
                                                        $rating = $ratings->firstWhere('rated_player_id', $ratedPlayer->id) ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $ratedPlayer->name }}</td>
                                                        <td class="text-end">
                                                            @if ($rating)
                                                                <span class="badge bg-primary">{{ $rating->rating_value }}</span>
                                                            @else
                                                                <span class="badge bg-dark-subtle">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteGameModal" tabindex="-1" aria-labelledby="deleteGameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGameModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete this game?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <form id="deleteGameForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(gameId, gameDate) {
            let form = document.getElementById('deleteGameForm');
            form.action = `/games/${gameId}`;

            let modalTitle = "{{ __('confirm.game_deletion') }}";
            document.getElementById('deleteGameModalLabel').innerText = modalTitle.replace(':date', gameDate);
        }

        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        document.querySelectorAll('.actions .btn').forEach(button => {
            button.addEventListener('click', () => {
                sessionStorage.setItem('scrollPosition', window.scrollY);
            });
        });
        window.onload = function() {
            const savedScrollPosition = sessionStorage.getItem('scrollPosition');
            if (savedScrollPosition) {
                window.scrollTo(0, savedScrollPosition);
                sessionStorage.removeItem('scrollPosition');
            }
        };
    </script>
@endsection
