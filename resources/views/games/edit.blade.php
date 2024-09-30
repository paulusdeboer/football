@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('extra-scripts')
    <!-- Styles for select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Scripts for select2 -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite(['resources/js/select2-bootstrap.js'])
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Games') }}</li>
            </ol>
        </div>
        <button type="submit" form="game-form" class="btn btn-primary mb-3">{{ __('Save Game') }}</button>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Edit Game') }}</div>
        <div class="card-body">
            <form method="POST" id="game-form" action="{{ route('games.update', $game->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label
                        for="played_at">{{ __('Select Game Date') }}</label>
                    <input type="date" id="played_at"
                           name="played_at" class="form-control mb-3"
                           value="{{ Carbon::parse($game->played_at)->format('Y-m-d') }}"
                           required>
                </div>

                <div class="form-group">
                    <label for="players">{{ __('Select 12 Players') }}</label>
                    <select id="players" name="players[]"
                            class="form-control form-select form-select-sm mb-3" multiple required size="12">
                        @foreach($players as $player)
                            <option value="{{ $player->id }}"
                                    @if(in_array($player->id, $selectedPlayers)) selected @endif>
                                {{ $player->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <a href="{{ route('games.index') }}"
               class="btn btn-secondary mt-3">{{ __('Back to Games List') }}</a>
        </div>
    </div>
@endsection
