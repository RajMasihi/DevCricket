@extends('layouts.main')
<title>@yield('title', 'Error Match')</title>
@section('main-container')
    <!-- <title>404 Not Found | CricketLiveScore</title> -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .error-404-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            padding: 30px 10px;
            background: #f5f8fb;
        }
        .error-404-code {
            font-size: 7rem;
            font-weight: bold;
            color: #0a3659;
            letter-spacing: 5px;
            margin-bottom: 16px;
        }
        .error-404-title {
            font-size: 2.4rem;
            color: #232323;
            font-weight: bold;
            margin-bottom: 16px;
        }
        .error-404-message {
            font-size: 1.2rem;
            margin-bottom: 32px;
            color: #294a71;
        }
        .error-404-home-link {
            display: inline-block;
            padding: 12px 28px;
            background: linear-gradient(90deg, #0a3659 0%, #053259 100%);
            color: #fff;
            font-weight: 600;
            border-radius: 25px;
            text-decoration: none;
            box-shadow: 0 4px 18px rgba(8, 48, 89, 0.10);
            transition: background 0.18s cubic-bezier(.59,2.51,.54,.78), color 0.18s;
        }
        .error-404-home-link:hover {
            color: #053259;
            background: #fff;
            box-shadow: 0 6px 16px rgba(10,54,89,.08);
        }
        .cricket-icon {
            font-size: 4rem;
            margin-bottom: 12px;
            color: #294a71;
        }
        @media (max-width: 500px) {
            .error-404-wrapper {
                padding: 24px 4px;
            }
            .error-404-code {
                font-size: 4rem;
            }
            .error-404-title {
                font-size: 1.55rem;
            }
            .cricket-icon {
                font-size: 2.5rem;
            }
        }
    </style>
    <div class="error-404-wrapper">
        <div class="cricket-icon">🏏</div>
        <div class="error-404-code">404</div>
        <div class="error-404-title">Page Not Found</div>
        <div class="error-404-message">
        @if($error)
            <span style="color:red;">{{ $error }}</span>
        @endif
            Oops! The page you are looking for doesn't exist or has been moved.<br>
            Please check the URL or head back to the homepage.
        </div>
        <a href="{{ url('/') }}" class="error-404-home-link">Back to Home</a>
    </div>
@endsection