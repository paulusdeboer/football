<form action="{{ isset($game) ? route('games.update', $game->id) : route('games.store') }}" id="game-form" method="POST">
    @csrf
    @if(isset($game))
        @method('PUT')
    @endif

    <div class="form-group">
        <label for="played_at">{{ __('Select game date') }}</label>
        <input type="text" id="played_at" name="played_at" class="form-control mb-3"
               value="{{ old('played_at', isset($game) ? \Carbon\Carbon::parse($game->played_at)->format('Y-m-d') : '') }}"
               placeholder="dd-mm-jjjj" required>
    </div>

    <div class="form-group">
        <label for="multiple-select-field">{{ __('Select 12 players') }}<span id="selected-count"> - 0 </span>{{ __('players selected') }}</label>
        <select name="players[]" id="multiple-select-field" class="form-control form-select mb-3" multiple required size="12">
            @foreach($players as $player)
                <option value="{{ $player->id }}" {{ isset($selectedPlayers) && in_array($player->id, $selectedPlayers) ? 'selected' : '' }}>
                    {{ $player->name }}
                </option>
            @endforeach
        </select>

        @error('players')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary mt-3">{{ __('Save') }}</button>
</form>

<a href="{{ route('games.index') }}" class="btn btn-secondary mt-3">{{ __('Back to games list') }}</a>

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
                width: '100%',
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false,
                dropdownCssClass: "select2--small",
            });

            $(document).ready(function () {
                function updateSelectedCount() {
                    let selectedCount = $('#multiple-select-field').select2('data').length;
                    $('#selected-count').text(' - ' + selectedCount + ' ');
                }

                updateSelectedCount();
                $('#multiple-select-field').on('change', updateSelectedCount);
            });

            clearInterval(waitForJQuery);
        }
    });
</script>
