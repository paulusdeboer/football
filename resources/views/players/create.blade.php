@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Players') }}</li>
            </ol>
        </div>
        <button type="submit" form="player-form" class="btn btn-primary mb-3">{{ __('Save') }}</button>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Create players') }}</div>

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
                            <select name="players[0][type]" class="form-control form-select player-type" required>
                                <option value="attacker">{{ __('Attacker') }}</option>
                                <option value="defender">{{ __('Defender') }}</option>
                                <option value="both">{{ __('Both') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Button to add more player rows -->
                <button type="button" class="btn btn-secondary" id="add-player-row">{{ __('Extra player') }}</button>
            </form>

            <a href="{{ route('players.index') }}" class="btn btn-secondary mt-3">{{ __('Back to players list') }}</a>
        </div>
    </div>

    <script>
        var waitForJQuery = setInterval(function () {
            if (window.jQuery) {
                // To keep track of the number of players
                let playerCount = 1;

                function initializeSelect2() {
                    $('select.player-type').select2( {
                        theme: "bootstrap-5",
                        width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                        placeholder: $( this ).data( 'placeholder' ),
                        closeOnSelect: true,
                    });
                }

                initializeSelect2();

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
                                <select name="players[${playerCount}][type]" class="form-control form-select player-type" required>
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

                    initializeSelect2();
                });

                // Function to remove a player row
                $(document).on('click', '.remove-player-row', function() {
                    $(this).closest('.player-row').remove();
                });

                clearInterval(waitForJQuery);
            }
        }, 10);
    </script>
@endsection
