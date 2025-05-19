@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Players') }}</li>
            </ol>
        </div>
        <a href="{{ route('players.create') }}" class="btn btn-primary mb-3">{{ __('Create player') }}</a>
    </div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                {{ __('Player list') }}
            </div>
            <form method="GET" action="{{ route('players.index') }}" class="float-end">
                <button type="submit" name="include_deleted"
                    value="{{ session('include_deleted', '0') == '1' ? '0' : '1' }}"
                    class="btn btn-sm {{ session('include_deleted', '0') == '1' ? 'btn-secondary' : 'btn-primary' }}">
                    {{ session('include_deleted', '0') == '1' ? __('Active players only') : __('All players (including inactive)') }}
                </button>
            </form>
        </div>

        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <a class="align-middle"
                                href="{{ route('players.index', ['sort_by' => 'name', 'sort_direction' => $sortBy === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Name') }}
                                {!! $sortBy === 'name' ? ($sortDirection === 'asc'
        ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
        : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
    ) : '' !!}
                            </a>
                        </th>
                        <th>
                            <a class="align-middle"
                                href="{{ route('players.index', ['sort_by' => 'email', 'sort_direction' => $sortBy === 'email' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Email') }} {!! $sortBy === 'email' ? ($sortDirection === 'asc'
        ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
        : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
    ) : '' !!}
                            </a>
                        </th>
                        <th>
                            <a class="align-middle"
                                href="{{ route('players.index', ['sort_by' => 'rating', 'sort_direction' => $sortBy === 'rating' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Rating') }} {!! $sortBy === 'rating' ? ($sortDirection === 'asc'
        ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
        : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
    ) : '' !!}
                            </a>
                        </th>
                        <th>
                            <a class="align-middle"
                                href="{{ route('players.index', ['sort_by' => 'type', 'sort_direction' => $sortBy === 'type' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Type') }} {!! $sortBy === 'type' ? ($sortDirection === 'asc'
        ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
        : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
    ) : '' !!}
                            </a>
                        </th>
                        <th>
                            <a class="align-middle"
                                href="{{ route('players.index', ['sort_by' => 'balance', 'sort_direction' => $sortBy === 'balance' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Balance') }} {!! $sortBy === 'balance' ? ($sortDirection === 'asc'
        ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
        : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
    ) : '' !!}
                            </a>
                        </th>
                        <th>
                            <a class="align-middle"
                                href="{{ route('players.index', ['sort_by' => 'created_at', 'sort_direction' => $sortBy === 'created_at' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Created at') }} {!! $sortBy === 'created_at' ? ($sortDirection === 'asc'
        ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
        : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
    ) : '' !!}
                            </a>
                        </th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($players as $player)
                        <tr>
                            <td>{{ $player->name }}</td>
                            <td>{{ $player->user?->email ?? __('No email') }}</td>
                            <td>{{ $player->rating }}</td>
                            <td>{{ ($player->type === 'attacker' ? __('Attacker') : ($player->type === 'defender' ? __('Defender') : __('Both'))) }}
                            </td>
                            <td>
                                €{{ number_format($player->balance ?? 0, 2, ',', '.') }}
                                <br>
                                <small class="text-muted">{{ number_format($player->getBalanceInGames(), 0, ',', '.') }}
                                    {{ __('games') }}</small>
                            </td>
                            <td>{{ Carbon::parse($player->created_at)->format('d-m-Y') }}</td>
                            <td class="actions">
                                <a href="{{ route('players.edit', $player) }}"
                                    class="btn btn-warning btn-sm">{{ __('Edit player') }}</a>
                                @if ($player->deleted_at)
                                    <form action="{{ route('players.restore', $player->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm">{{ __('Restore') }}</button>
                                    </form>
                                @else
                                    <button class="btn btn-danger btn-sm"
                                        onclick="confirmDelete({{ $player->id }}, '{{ addslashes($player->name) }}')"
                                        data-bs-toggle="modal" data-bs-target="#deletePlayerModal">
                                        {{ __('Delete player') }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePlayerModal" tabindex="-1" aria-labelledby="deletePlayerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePlayerModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete this player?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <form id="deletePlayerForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(playerId, playerName) {
            let form = document.getElementById('deletePlayerForm');
            form.action = `/players/${playerId}`;

            let modalTitle = "{{ __('confirm.player_deletion') }}";
            document.getElementById('deletePlayerModalLabel').innerText = modalTitle.replace(':name', playerName);
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