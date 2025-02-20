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
        <a href="{{ route('games.create') }}" class="btn btn-primary mb-3">{{ __('Create game') }}</a>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            {{ __('Games list') }}
        </div>

        <div class="card-body">

            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Game date') }}</th>
                    <th>{{ __('Team 1 score') }}</th>
                    <th>{{ __('Team 2 score') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($games as $game)
                    <tr>
                        <td>{{ Carbon::parse($game->played_at)->format('d-m-Y') }}</td>
                        <td>{{ $game->team1_score }}</td>
                        <td>{{ $game->team2_score }}</td>
                        <td class="actions">
                            <a href="{{ route('games.show', $game) }}"
                               class="btn btn-info btn-sm">{{ __('View') }}</a>
                            @if ($game->team1_score === null && $game->team2_score === null)
                                <a href="{{ route('games.edit', $game) }}"
                                   class="btn btn-warning btn-sm">{{ __('Edit game') }}</a>
                                <a href="{{ route('games.enter-result', $game) }}"
                                   class="btn btn-warning btn-sm">{{ __('Enter result') }}</a>
                                <button class="btn btn-danger btn-sm"
                                        onclick="confirmDelete({{ $game->id }}, '{{ addslashes(Carbon::parse($game->played_at)->format('d-m-Y')) }}')"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteGameModal">
                                    {{ __('Delete game') }}
                                </button>
                            @else
                                @if ($game->ratings->isNotEmpty())
                                    <div class="d-inline-block" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Players have already submitted ratings for this game') }}">
                                        <button class="btn btn-warning btn-sm" disabled>{{ __('Edit result') }}</button>
                                    </div>
                                @else
                                    <a href="{{ route('games.enter-result', $game) }}"
                                       class="btn btn-warning btn-sm">
                                        {{ __('Edit result') }}
                                    </a>
                                @endif
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
