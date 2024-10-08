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
                <li class="breadcrumb-item active">{{ __('Players') }}</li>
            </ol>
        </div>
        <button type="submit" form="player-form" class="btn btn-primary mb-3">{{ __('Create Players') }}</button>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Create New Players') }}</div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form for creating multiple players -->
            <form action="{{ route('players.store') }}" id="player-form" method="POST">
                @csrf

                <div id="player-rows">
                    <div class="row mb-3 player-row">
                        <div class="col-3">
                            <input type="text" name="players[0][name]" class="form-control" placeholder="{{ __('Name') }}" required>
                        </div>
                        <div class="col-3">
                            <input type="email" name="players[0][email]" class="form-control" placeholder="{{ __('Email') }}" required>
                        </div>
                        <div class="col-3">
                            <input type="number" name="players[0][rating]" class="form-control" placeholder="{{ __('Rating') }}" required min="5.0" max="10.0" step="0.1">
                        </div>
                        <div class="col-2">
                            <select name="players[0][type]" class="form-control form-select" required>
                                <option value="attacker">{{ __('Attacker') }}</option>
                                <option value="defender">{{ __('Defender') }}</option>
                                <option value="both">{{ __('Both') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Button to add more player rows -->
                <button type="button" class="btn btn-secondary" id="add-player-row">{{ __('Extra Player') }}</button>
            </form>

            <a href="{{ route('players.index') }}" class="btn btn-secondary mt-3">{{ __('Back to Players List') }}</a>
        </div>
    </div>

    <script>
        // To keep track of the number of players
        let playerCount = 1;

        // Function to add a new player row
        $('#add-player-row').click(function() {
            let newRow = `
                <div class="row mb-3 player-row">
                    <div class="col-3">
                        <input type="text" name="players[${playerCount}][name]" class="form-control" placeholder="{{ __('Name') }}" required>
                    </div>
                    <div class="col-3">
                        <input type="email" name="players[${playerCount}][email]" class="form-control" placeholder="{{ __('Email') }}" required>
                    </div>
                    <div class="col-3">
                        <input type="number" name="players[${playerCount}][rating]" class="form-control" placeholder="{{ __('Rating') }}" required min="5.0" max="10.0" step="0.1">
                    </div>
                    <div class="col-2">
                        <select name="players[${playerCount}][type]" class="form-control form-select" required>
                            <option value="attacker">{{ __('Attacker') }}</option>
                            <option value="defender">{{ __('Defender') }}</option>
                            <option value="both">{{ __('Both') }}</option>
                        </select>
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-danger remove-player-row">{{ __('Remove') }}</button>
                    </div>
                </div>`;

            $('#player-rows').append(newRow);
            playerCount++;
        });

        // Function to remove a player row
        $(document).on('click', '.remove-player-row', function() {
            $(this).closest('.player-row').remove();
        });
    </script>
@endsection
