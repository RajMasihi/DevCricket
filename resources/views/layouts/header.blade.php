<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">

    {{-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> --}}
    <title>CricketLiveData</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('js/cricket_js.js') }}"></script>
  
</head>

<body>
    <header class="header-bar">
        <div class="first-header">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <a style="color:#fff" class="navbar-brand link" href="#">CricketLiveData</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                        <ul class="navbar-nav first-nav">
                            <li class="nav-item">
                                <a class="nav-link link active" aria-current="page"
                                    href="{{ asset('/') }}">Live Score</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link link" href="{{ asset("/sduling") }}">Upcoming Matchs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" href="{{ asset("/result") }}">Result</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" href="{{ asset("/series") }}">series</a>
                            </li>
                          
                            <li class="nav-item dropdown">
                                <a class="nav-link link dropdown-toggle" href="#"
                                    id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    Teams
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                                    {{-- @foreach ($teams->data as $team)
                                    <li><a class="dropdown-item" href="{{ $team->id }}"><img class="w-10 h-10" src="{{$team->genericFleg}}" alt=""> &nbsp;&nbsp;{{ $team->name }}</a></li>
                                    @endforeach --}}
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" href="{{ asset("/news") }}">News</a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link link" href="{{ asset("/gallary") }}">Gallery</a>
                            </li> --}}
                            <li class="nav-item">
                                <a class="nav-link link" href="{{ asset("/leatest") }}">Leatest Series</a>
                            </li>
                        </ul>
                    </div>
                    <div class="search" style="display: none"><input id="search" type="text" style=""></div>
                </div>
            </nav>
        </div>
        <div class="second-header">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="navbar-collapse justify-content-center" id="navbarNavDropdownsecond">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link link active" aria-current="page" >All</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" >International</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" >Domestic</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" >T20</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link link" >ODI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" >Test</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" >Mens</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link link" >Women</a>
                            </li>
                            
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </header>
<div class="search-items">