<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <title>{{ __('app_name') }}</title>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @yield('extra-scripts')
</head>
<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="{{ route('dashboard') }}">
        <svg stroke="currentColor" fill="currentColor" stroke-width="0"
             viewBox="0 0 512 512" height="20px" width="20px"
             xmlns="http://www.w3.org/2000/svg">
            <path
                d="M255.03 33.813c-1.834-.007-3.664-.007-5.5.03-6.73.14-13.462.605-20.155 1.344.333.166.544.32.47.438L204.78 75.063l73.907 49.437-.125.188 70.625.28L371 79.282 342.844 52c-15.866-6.796-32.493-11.776-49.47-14.78-12.65-2.24-25.497-3.36-38.343-3.407zM190.907 88.25l-73.656 36.78-13.813 98.407 51.344 33.657 94.345-43.438 14.875-76.5-73.094-48.906zm196.344.344l-21.25 44.5 36.75 72.72 62.063 38.905 11.312-21.282c.225.143.45.403.656.75-.77-4.954-1.71-9.893-2.81-14.782-6.446-28.59-18.59-55.962-35.5-79.97-9.07-12.872-19.526-24.778-31.095-35.5l-20.125-5.342zm-302.656 23c-6.906 8.045-13.257 16.56-18.938 25.5-15.676 24.664-26.44 52.494-31.437 81.312C31.783 232.446 30.714 246.73 31 261l20.25 5.094 33.03-40.5L98.75 122.53l-14.156-10.936zm312.719 112.844l-55.813 44.75-3.47 101.093 39.626 21.126 77.188-49.594 4.406-78.75-.094.157-61.844-38.783zm-140.844 6.406l-94.033 43.312-1.218 76.625 89.155 57.376 68.938-36.437 3.437-101.75-66.28-39.126zm-224.22 49.75c.91 8.436 2.29 16.816 4.156 25.094 6.445 28.59 18.62 55.96 35.532 79.968 3.873 5.5 8.02 10.805 12.374 15.938l-9.374-48.156.124-.032-27.03-68.844-15.782-3.968zm117.188 84.844l-51.532 8.156 10.125 52.094c8.577 7.49 17.707 14.332 27.314 20.437 14.612 9.287 30.332 16.88 46.687 22.594l62.626-13.69-4.344-31.124-90.875-58.47zm302.437.5l-64.22 41.25-42 47.375 4.408 6.156c12.027-5.545 23.57-12.144 34.406-19.72 23.97-16.76 44.604-38.304 60.28-62.97 2.51-3.947 4.87-7.99 7.125-12.092zm-122.78 97.656l-79.94 9.625-25.968 5.655c26.993 4 54.717 3.044 81.313-2.813 9.412-2.072 18.684-4.79 27.75-8.062l-3.156-4.406z">
            </path>
        </svg>
        {{ __('Friday football app') }}
    </a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0"
            id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
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
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#"
               role="button" data-bs-toggle="dropdown" aria-expanded="false"><i
                    class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end"
                aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li><a class="dropdown-item" href="#">Activity Log</a></li>
                <li>
                    <hr class="dropdown-divider"/>
                </li>
                <li><a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                </li>
                <form id="logout-form" action="{{ route('logout') }}"
                      method="POST" class="d-none">
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
                    <div
                        class="sb-sidenav-menu-heading">{{ __('Statistics') }}</div>
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <div class="sb-nav-link-icon"><i
                                class="fas fa-tachometer-alt"></i></div>
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
                        <div class="sb-nav-link-icon"><i
                                class="fas fa-table"></i></div>
                        {{ __('Games') }}
                    </a>
                    <a class="nav-link" href="{{ route('ratings.index') }}">
                        <div class="sb-nav-link-icon"><i
                                class="fas fa-table"></i></div>
                        {{ __('Given ratings') }}
                    </a>
                    <a class="nav-link" href="{{ route('games.create') }}">
                        <div class="sb-nav-link-icon"><i
                                class="fa-regular fa-calendar-plus"></i></div>
                        {{ __('Create game') }}
                    </a>
                    <div
                        class="sb-sidenav-menu-heading">{{ __('Players') }}</div>
                    <a class="nav-link" href="{{ route('players.index') }}">
                        <div class="sb-nav-link-icon"><i
                                class="fas fa-table"></i></div>
                        {{ __('Players') }}
                    </a>
                    <a class="nav-link" href="{{ route('players.create') }}">
                        <div class="sb-nav-link-icon"><i
                                class="fa-solid fa-user-plus"></i></div>
                        {{ __('Add players') }}
                    </a>
                    <div
                        class="sb-sidenav-menu-heading">{{ __('Financial') }}</div>
                    <a class="nav-link"
                       href="{{ route('transactions.index') }}">
                        <div class="sb-nav-link-icon"><i
                                class="fas fa-table"></i></div>
                        {{ __('Transactions') }}
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
