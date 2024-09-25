<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

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
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">{{ __('Confirm Password') }}</h3></div>
                                    <div class="card-body">
                                        <p class="text-center">{{ __('Please confirm your password before continuing.') }}</p>

                                        <form method="POST" action="{{ route('password.confirm') }}">
                                            @csrf

                                            <div class="form-floating mb-3">
                                                <input class="form-control @error('password') is-invalid @enderror" id="inputPassword" type="password" name="password" required autocomplete="current-password" placeholder="Password">
                                                <label for="inputPassword">{{ __('Password') }}</label>

                                                @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                @enderror
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    {{ __('Confirm Password') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        @if (Route::has('password.request'))
                                            <div class="small">
                                                <a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                                            </div>
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
