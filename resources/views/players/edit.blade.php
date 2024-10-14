@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Player') }}</li>
            </ol>
        </div>
        <button type="submit" form="player-form" class="btn btn-primary mb-3">{{ __('Save Player') }}</button>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Edit Player') }}</div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif
            <form method="POST" id="player-form" action="{{ route('players.update', $player->id) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3 player-row">
                    <div class="col-3">
                        <input type="text" name="player_name" class="form-control" placeholder="{{ __('Name') }}" required>
                    </div>
                    <div class="col-3">
                        <input type="email" name="player_email" class="form-control" placeholder="{{ __('Email') }}" required>
                    </div>
                    <div class="col-3">
                        <input type="number" name="player_rating" class="form-control" placeholder="{{ __('Rating') }}" required min="5.0" max="10.0" step="0.1">
                    </div>
                    <div class="col-2">
                        <select name="player_type" class="form-control form-select player-type" required>
                            <option value="attacker">{{ __('Attacker') }}</option>
                            <option value="defender">{{ __('Defender') }}</option>
                            <option value="both">{{ __('Both') }}</option>
                        </select>
                    </div>
                </div>
            </form>

            <a href="{{ route('players.index') }}"
               class="btn btn-secondary mt-3">{{ __('Back to Players List') }}</a>
        </div>
    </div>
    <script>
        var waitForJQuery = setInterval(function () {
            if (window.jQuery) {
                $('select.player-type').select2({
                    theme: "bootstrap-5",
                    width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                    placeholder: $(this).data('placeholder'),
                    closeOnSelect: true,
                });

                clearInterval(waitForJQuery);
            }
        });
    </script>
@endsection
