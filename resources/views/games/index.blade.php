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
        <a href="{{ route('games.create') }}"
           class="btn btn-primary mb-3">{{ __('Create game') }}</a>
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
                    @php
                        $columns = [
                            'played_at' => __('Game date'),
                            'team1_score' => __('Team 1 score'),
                            'team2_score' => __('Team 2 score'),
                        ];
                    @endphp

                    @foreach ($columns as $column => $label)
                        @php
                            $isSorted = $sortBy === $column;
                            $newDirection = $isSorted && $sortDirection === 'asc' ? 'desc' : 'asc';
                            $icon = $isSorted
                                ? ($sortDirection === 'asc'
                                    ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
                                    : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>')
                                : '';
                        @endphp

                        <th>
                            <a class="align-middle text-decoration-none text-dark"
                               href="{{ route('games.index', array_merge(request()->query(), ['sort_by' => $column, 'sort_direction' => $newDirection])) }}">
                                {!! $label !!} {!! $icon !!}
                            </a>
                        </th>
                    @endforeach
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
                                <a href="{{ route('games.enter-result', $game) }}"
                                   class="btn btn-warning btn-sm">
                                    {{ __('Edit result') }}
                                </a>
                            @endif

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteGameModal" tabindex="-1"
         aria-labelledby="deleteGameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGameModalLabel"></h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete this game?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <form id="deleteGameForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-danger">{{ __('Delete') }}</button>
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

        document.querySelectorAll('.actions .btn').forEach(button => {
            button.addEventListener('click', () => {
                sessionStorage.setItem('scrollPosition', window.scrollY);
            });
        });
        window.onload = function () {
            const savedScrollPosition = sessionStorage.getItem('scrollPosition');
            if (savedScrollPosition) {
                window.scrollTo(0, savedScrollPosition);
                sessionStorage.removeItem('scrollPosition');
            }
        };
    </script>
@endsection
