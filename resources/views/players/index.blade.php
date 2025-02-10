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
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            {{ __('Player list') }}
        </div>

        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Rating') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Created at') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($players as $player)
                    <tr>
                        <td>{{ $player->name }}</td>
                        <td>{{ $player->user?->email ?? __('No email') }}</td>
                        <td>{{ $player->rating }}</td>
                        <td>{{ ($player->type === 'attacker' ? __('Attacker') : ($player->type === 'defender' ? __('Defender') : __('Both'))) }}</td>
                        <td>{{ Carbon::parse($player->created_at)->format('d-m-Y') }}</td>
                        <td>
                            <a href="{{ route('players.edit', $player) }}"
                               class="btn btn-warning btn-sm">{{ __('Edit player') }}</a>
                            <button class="btn btn-danger btn-sm"
                                    onclick="confirmDelete({{ $player->id }}, '{{ addslashes($player->name) }}')"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deletePlayerModal">
                                {{ __('Delete player') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePlayerModal" tabindex="-1" aria-labelledby="deletePlayerModalLabel" aria-hidden="true">
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

    </script>
@endsection
