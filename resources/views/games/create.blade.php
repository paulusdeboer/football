@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Create a New Game') }}</div>

                    <div class="card-body">
                        <form action="{{ route('games.store') }}" method="POST">
                            @csrf

                            <!-- Date selection -->
                            <div class="form-group">
                                <label for="played_at">{{ __('Select Game Date') }}:</label>
                                <input type="date" id="played_at" name="played_at" class="form-control" required>
                            </div>

                            <!-- Player selection -->
                            <div class="form-group">
                                <label for="players">{{ __('Select 12 Players:') }}</label>
                                <select name="players[]" id="players" class="form-control" multiple required size="12">
                                    @foreach($players as $player)
                                        <option value="{{ $player->id }}">{{ $player->name }}</option>
                                    @endforeach
                                </select>

                                @error('players')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('Create Game') }}</button>
                        </form>
                        <a href="{{ route('games.index') }}" class="btn btn-secondary mt-3">{{ __('Back to Games List') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


