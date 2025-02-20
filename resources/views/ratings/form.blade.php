@php use Carbon\Carbon; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-primary">
<div id="layoutAuthentication">
    <div id="layoutAuthentication_content">
        <main>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card shadow-lg border-0 rounded-lg my-5">
                            <div class="card-header">
                                <h3 class="text-center font-weight-light my-4">{{ __('Hello') }} {{ $player->name }}, {{ __('rate players for the game on') }} {{ Carbon::parse($game->played_at)->format('d-m-Y') }}</h3>
                                <p class="text-center">{{ __('Give your rating for each player between 5 and 10 with 5 being the lowest and 10 being the best.') }}</p>
                            </div>
                            <div class="card-body">
                                @if ($hasRated)
                                    <p class="text-center">{{ __('You have already submitted a rating for this game.') }}</p>
                                @else
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form action="{{ URL::temporarySignedRoute('ratings.store', now()->addHours(72), ['game' => $game->id, 'player' => $player->id]) }}" method="POST">

                                        @csrf

                                        <div class="row">
                                            <p><strong>{{ __('Result') . ': ' }}</strong> {{ $game->team1_score ? $game->team1_score . ' - ' . $game->team2_score : ''}}</p>

                                            <div class="col">
                                                <h5>{{ __('Team 1') }}</h5>
                                                <div class="form-group">
                                                    @foreach($team1Players as $team1Player)
                                                        <div class="mb-3">
                                                            <label for="rating_{{ $team1Player->id }}">{{ $team1Player->name }}</label>
                                                            <input type="number" name="ratings[{{ $team1Player->id }}]" id="rating_{{ $team1Player->id }}"
                                                                   class="form-control" min="5" max="10" step="0.1"
                                                                   {{ $team1Player->id == $player->id ? 'disabled' : '' }}
                                                                   placeholder="{{ __('Enter rating') }}">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="col">
                                                <h5>{{ __('Team 2') }}</h5>
                                                <div class="form-group">
                                                    @foreach($team2Players as $team2Player)
                                                        <div class="mb-3">
                                                            <label for="rating_{{ $team2Player->id }}">{{ $team2Player->name }}</label>
                                                            <input type="number" name="ratings[{{ $team2Player->id }}]" id="rating_{{ $team2Player->id }}"
                                                                   class="form-control" min="5" max="10" step="0.1"
                                                                   {{ $team2Player->id == $player->id ? 'disabled' : '' }}
                                                                   placeholder="{{ __('Enter rating') }}">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">{{ __('Submit ratings') }}</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
