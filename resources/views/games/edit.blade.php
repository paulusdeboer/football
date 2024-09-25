@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Edit Game') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('games.update', $game->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="played_at">{{ __('Select Game Date') }}</label>
                                <input type="date" id="played_at" name="played_at" class="form-control"
                                       value="{{ \Carbon\Carbon::parse($game->played_at)->format('Y-m-d') }}" required>
                            </div>

                            <div class="form-group">
                                <label for="players">Select Players:</label>
                                <select id="players" name="players[]" class="form-control" multiple required>
                                    @foreach($players as $player)
                                        <option value="{{ $player->id }}"
                                                @if(in_array($player->id, $selectedPlayers)) selected @endif>
                                            {{ $player->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('Update Game') }}</button>
                        </form>

                        <a href="{{ route('games.index') }}" class="btn btn-secondary mt-3">{{ __('Back to Games List') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
