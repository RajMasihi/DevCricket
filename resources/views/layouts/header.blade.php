<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="X-UA-Compatible" content="IE=edge">

    {{-- Dynamic Page Title --}}
    <title>@yield('title', 'Criclivem - Live Cricket Scores, Match Updates, Schedule & News')</title>

    {{-- Meta Description --}}
    @yield('meta-description')
    <meta name="description" content="@yield('meta-description-content', 'Criclivem - Live cricket scores, match updates, schedules, news, and ICC rankings. Stay updated with latest cricket action from around the world including international matches, domestic leagues, and women cricket.')">

    {{-- Meta Keywords --}}
    @yield('meta-keywords')
    <meta name="keywords" content="@yield('meta-keywords-content', 'cricket, live cricket scores, cricket news, ICC rankings, cricket schedule, T20, ODI, Test cricket, IPL, BBL, CPL, international cricket, domestic cricket, women cricket')">

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Robots Meta --}}
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="@yield('title', 'Criclivem - Live Cricket Scores, Match Updates, Schedule & News')">
    <meta property="og:description" content="@yield('meta-description-content', 'Criclivem - Live cricket scores, match updates, schedules, news, and ICC rankings. Stay updated with latest cricket action from around the world.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og-image', asset('images/criclivem-og.jpg'))">
    <meta property="og:site_name" content="Criclivem">
    <meta property="og:locale" content="en_US">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Criclivem - Live Cricket Scores, Match Updates, Schedule & News')">
    <meta name="twitter:description" content="@yield('meta-description-content', 'Criclivem - Live cricket scores, match updates, schedules, news, and ICC rankings.')">
    <meta name="twitter:image" content="@yield('og-image', asset('images/criclivem-og.jpg'))">
    <meta name="twitter:site" content="@criclivem">

    {{-- Additional SEO Meta Tags --}}
    <meta name="theme-color" content="#053259">
    <meta name="msapplication-TileColor" content="#053259">
    <link rel="preconnect" href="https://static.cricbuzz.com">
    <link rel="dns-prefetch" href="https://static.cricbuzz.com">

    {{-- Laravel Reverb Configuration --}}
    <meta name="reverb-app-key" content="{{ env('REVERB_APP_KEY') }}">
    <meta name="reverb-host" content="{{ env('REVERB_HOST', request()->getHost()) }}">
    <meta name="reverb-port" content="{{ env('REVERB_PORT', 8080) }}">
    <meta name="reverb-scheme" content="{{ env('REVERB_SCHEME', request()->secure() ? 'https' : 'http') }}">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('js/cricket_js.js') }}"></script>
    <script src="{{ asset('js/over_threshold_handler.js') }}"></script>

</head>

<body>
    <header class="header-bar">
        <div class="first-header">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <!-- update code -->
                    <a style="color:#fff" class="navbar-brand link" href="{{ asset('/') }}">Criclivem</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                        <ul class="navbar-nav first-nav">
                            <li class="nav-item">
                                <a class="nav-link link active" aria-current="page"
                                    href="{{ route('home') }}">Live Score</a>
                            </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link link" href="{{ route('upcoming-matches') }}">Upcoming</a>
                            </li> -->
                            <li class="nav-item">
                                <a class="nav-link link" href="{{ route('schedule-international') }}">schedule</a>
                            </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link link" href="{{ route('match-results') }}">Result</a>
                            </li> -->
                            <li class="nav-item">
                                <a class="nav-link link" href="{{ route('series') }}">series</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link link dropdown-toggle" href="#"
                                    id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    Teams
                                </a>
                                <ul class="dropdown-menu team-dropdown-menu" aria-labelledby="navbarDropdownMenuLink">

                                    <li>
                                        <a class="dropdown-item" href="{{ route('teams-international') }}">International</a>
                                        <a class="dropdown-item" href="{{ route('teams-domestic') }}">Domestic</a>
                                        <a class="dropdown-item" href="{{ route('teams-league') }}">League</a>
                                        <a class="dropdown-item" href="{{ route('teams-womens') }}">Womens</a>
                                    </li>
                                </ul>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link link" href="{{ route('news') }}">news</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link link dropdown-toggle" href="#"
                                    id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    ICC Ranks
                                </a>
                                <ul class="dropdown-menu team-dropdown-menu" aria-labelledby="navbarDropdownMenuLink">

                                    <li>
                                        <a class="dropdown-item" href="{{ route('icc-rankings-mens') }}">In't Rank Mens</a>
                                        <a class="dropdown-item" href="{{ route('icc-rankings-womens') }}">In't Rank Womens</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">&nbsp;&nbsp;
                                <!-- <a class="nav-link link" href="{{ asset('/serieslist/10119/icc-womens-t20-world-cup-2026') }}">Womens WC 2026</a> -->
                            </li>
                        </ul>
                    </div>
                    <div class="search" style="display: none"><input id="search" type="text" style=""></div>
                </div>
            </nav>
        </div>
        <div class="second-header">
            <!-- <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="navbar-collapse justify-content-center" id="navbarNavDropdownsecond">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link link active" aria-current="page">All</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link">International</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link">Domestic</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link">T20</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link">ODI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link">Test</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link">Mens</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link">Women</a>
                            </li>
                            @if(isset($series['name']) && !empty($series['name']))
                                <li class="nav-item">
                                    @php
                                        // Always check for id first, then fallback to series_id if not empty
                                        $pointTableId = '';
                                        if (isset($series['id']) && !empty($series['id'])) {
                                            $pointTableId = $series['id'];
                                        } elseif (isset($series['series_id']) && !empty($series['series_id'])) {
                                            $pointTableId = $series['series_id'];
                                        }
                                    @endphp
                                    @if(!empty($pointTableId))
                                       <a class="point-table-nav nav-link link" href="javascript:void(0);" id="point-table"
                                            data-seriesid="{{ $pointTableId }}">Point Table</a> 
                                            <a class="point-table-nav nav-link link" href="{{url('/point-table')}}/{{ $pointTableId }}" id="point-table"
                                            data-seriesid="{{ $pointTableId }}">Point Table</a>
                                    @endif
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </nav> -->
        </div>
    </header>
    <div class="search-items">
        <!-- Container for AJAX point table, hidden by default -->
        <div class="container point-table-ajax" style="display:none;"></div>
    </div>