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
        <button type="submit" form="game-form" class="btn btn-primary mb-3">{{ __('Save game') }}</button>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Edit game') }}</div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif
            <form method="POST" id="game-form" action="{{ route('games.update', $game->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label
                        for="played_at">{{ __('Select game date') }}</label>
                    <input type="date" id="played_at"
                           name="played_at" class="form-control mb-3"
                           value="{{ Carbon::parse($game->played_at)->format('Y-m-d') }}"
                           required>
                </div>

                <div class="form-group">
                    <label for="multiple-select-field">{{ __('Select 12 players') }}</label>
                    <select id="multiple-select-field" name="players[]"
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
    <script>
        var waitForJQuery = setInterval(function () {
            if (window.jQuery) {
                $('#multiple-select-field').select2({
                    theme: "bootstrap-5",
                    width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                    placeholder: $(this).data('placeholder'),
                    closeOnSelect: false,
                });
                $(document).ready(function () {
                    function updateSelectedCount() {
                        let selectedCount = $('#multiple-select-field').select2('data').length;
                        $('#selected-count').text(' - ' + selectedCount + ' ');
                    }

                    updateSelectedCount();

                    $('#multiple-select-field').on('change', function () {
                        updateSelectedCount();
                    });
                });

                clearInterval(waitForJQuery);
            }
        });
    </script>
@endsection
