<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Football app') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @yield('extra-scripts')
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="{{ route('dashboard') }}"><i class="fa-regular fa-futbol"></i> {{ __('Football app') }}</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
{{--        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">--}}
{{--            <div class="input-group">--}}
{{--                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />--}}
{{--                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>--}}
{{--            </div>--}}
{{--        </form>--}}
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><a class="dropdown-item" href="#">Activity Log</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a></li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">{{ __('Statistics') }}</div>
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            {{ __('Dashboard') }}
                        </a>
{{--                        <div class="sb-sidenav-menu-heading">{{ __('Layout') }}</div>--}}
{{--                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">--}}
{{--                            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>--}}
{{--                            Layouts--}}
{{--                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>--}}
{{--                        </a>--}}
{{--                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">--}}
{{--                            <nav class="sb-sidenav-menu-nested nav">--}}
{{--                                <a class="nav-link" href="layout-static.html">Static Navigation</a>--}}
{{--                                <a class="nav-link" href="layout-sidenav-light.html">Light Sidenav</a>--}}
{{--                            </nav>--}}
{{--                        </div>--}}
{{--                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">--}}
{{--                            <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>--}}
{{--                            Pages--}}
{{--                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>--}}
{{--                        </a>--}}
{{--                        <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">--}}
{{--                            <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">--}}
{{--                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">--}}
{{--                                    Authentication--}}
{{--                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>--}}
{{--                                </a>--}}
{{--                                <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">--}}
{{--                                    <nav class="sb-sidenav-menu-nested nav">--}}
{{--                                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>--}}
{{--                                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>--}}
{{--                                        <a class="nav-link" href="{{ route('password.request') }}">{{ __('Forgot Password') }}</a>--}}
{{--                                    </nav>--}}
{{--                                </div>--}}
{{--                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">--}}
{{--                                    Error--}}
{{--                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>--}}
{{--                                </a>--}}
{{--                                <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">--}}
{{--                                    <nav class="sb-sidenav-menu-nested nav">--}}
{{--                                        <a class="nav-link" href="401.html">401 Page</a>--}}
{{--                                        <a class="nav-link" href="404.html">404 Page</a>--}}
{{--                                        <a class="nav-link" href="500.html">500 Page</a>--}}
{{--                                    </nav>--}}
{{--                                </div>--}}
{{--                            </nav>--}}
{{--                        </div>--}}
                        <div class="sb-sidenav-menu-heading">{{ __('Games') }}</div>
                        <a class="nav-link" href="{{ route('games.index') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            {{ __('Games') }}
                        </a>
                        <a class="nav-link" href="{{ route('games.create') }}">
                            <div class="sb-nav-link-icon"><i class="fa-regular fa-calendar-plus"></i></div>
                            {{ __('Create a game') }}
                        </a>
                        <div class="sb-sidenav-menu-heading">{{ __('Players') }}</div>
                        <a class="nav-link" href="tables.html">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            {{ __('Players') }}
                        </a>
                        <a class="nav-link" href="{{ route('games.create') }}">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-user-plus"></i></div>
                            {{ __('Add a player') }}
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">{{ __('Logged in as') }}:</div>
                    {{ $user->name }}
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    @yield('content')
                </div>
            </main>
{{--            <footer class="py-4 bg-light mt-auto">--}}
{{--                <div class="container-fluid px-4">--}}
{{--                    <div class="d-flex align-items-center justify-content-between small">--}}
{{--                        <div class="text-muted">Copyright &copy; Your Website 2023</div>--}}
{{--                        <div>--}}
{{--                            <a href="#">Privacy Policy</a>--}}
{{--                            &middot;--}}
{{--                            <a href="#">Terms &amp; Conditions</a>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </footer>--}}
        </div>
    </div>
</body>
</html>
