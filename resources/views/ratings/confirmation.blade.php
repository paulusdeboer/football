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
                                        <h3 class="text-center font-weight-light my-4">{{ __('Thank you, ') . $player->name . '!' }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-center">
                                            {{ __('Thank you for submitting your ratings for the game of ') . Carbon::parse($game->played_at)->format('d-m-Y') . '.' }}
                                        </p>
                                        <p class="text-center">{{ __('Your feedback is valuable and helps us improve the game experience.') }}</p>
                                        <p class="text-center">{{ __('You may close this page now.') }}</p>
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
