@extends('layouts.app')

@section('extra-scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Games') }}</li>
            </ol>
        </div>
        <button type="submit" form="game-form" class="btn btn-primary mb-3">{{ __('Save') }}</button>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Create a new game') }}</div>

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
            <form action="{{ route('games.store') }}" id="game-form" method="POST">
                @csrf

                <!-- Date selection -->
                <div class="form-group">
                    <label for="played_at">{{ __('Select game date') }}:</label>
                    <input type="text" id="played_at" name="played_at" class="form-control mb-3" placeholder="dd-mm-jjjj" required>
                </div>

                <!-- Player selection -->
                <div class="form-group">
                    <label for="multiple-select-field">{{ __('Select 12 players') }}<span id="selected-count"> - 0 </span>{{ __('players selected') }}</label>
                    <select name="players[]" id="multiple-select-field" class="form-control form-select mb-3" multiple required size="12">
                        @foreach($players as $player)
                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                        @endforeach
                    </select>

                    @error('players')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </form>
            <a href="{{ route('games.index') }}" class="btn btn-secondary mt-3">{{ __('Back to games list') }}</a>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#played_at", {
                minDate: "today",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                allowInput: true,
                clickOpens: true
            });
        });

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


