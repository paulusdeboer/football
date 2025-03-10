@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Transactions') }}</li>
            </ol>
        </div>
        <a href="{{ route('transactions.create') }}"
           class="btn btn-primary mb-3">{{ __('Add transaction') }}</a>
    </div>
    <div class="card mb-4">
        <div
            class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                {{ __('Transaction list') }}
            </div>
            {{--            <form method="GET" action="{{ route('transactions.index') }}" class="float-end">--}}
            {{--                <button type="submit" name="include_deleted" value="{{ session('include_deleted', '0') == '1' ? '0' : '1' }}"--}}
            {{--                        class="btn btn-sm {{ session('include_deleted', '0') == '1' ? 'btn-secondary' : 'btn-primary' }}">--}}
            {{--                    {{ session('include_deleted', '0') == '1' ? __('Active players only') : __('All players (including inactive)') }}--}}
            {{--                </button>--}}
            {{--            </form>--}}
        </div>

        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>
                        <a class="align-middle"
                           href="{{ route('transactions.index', ['sort_by' => 'date', 'sort_direction' => $sortBy === 'date' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Date') }}
                            {!! $sortBy === 'date' ? ($sortDirection === 'asc'
                                ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
                                : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
                            ) : '' !!}
                        </a>
                    </th>
                    <th>
                        <a class="align-middle"
                           href="{{ route('transactions.index', ['sort_by' => 'player_id', 'sort_direction' => $sortBy === 'player_id' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Player') }} {!! $sortBy === 'player_id' ? ($sortDirection === 'asc'
                                ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
                                : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
                            ) : '' !!}
                        </a>
                    </th>
                    <th>
                        <a class="align-middle"
                           href="{{ route('transactions.index', ['sort_by' => 'amount', 'sort_direction' => $sortBy === 'amount' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Amount') }} {!! $sortBy === 'amount' ? ($sortDirection === 'asc'
                                ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
                                : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1.5.3.7c.2.2.4.3.7.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
                            ) : '' !!}
                        </a>
                    </th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                        <td class="actions">
                            <a href="{{ route('transactions.edit', $transaction) }}"
                               class="btn btn-warning btn-sm">{{ __('Edit player') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePlayerModal" tabindex="-1"
         aria-labelledby="deletePlayerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePlayerModalLabel"></h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete this player?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <form id="deletePlayerForm" method="POST">
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
