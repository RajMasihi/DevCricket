@extends('layouts.main')
<title>@yield('title', 'Upcoming Match')</title>
@section('main-container')
    <div class="container-fluit main-section">
        <h3 class="text-center">Cricket Live Score Upcoming Matchs</h3>
        <div class="row row-cols-1 row-cols-md-2 g-4 pt-3">

            @foreach ($sdulingmatchs as $sdule)
                @php
                    $dateTime = $sdule['dateTimeGMT'];
                    $matchDate = date('Y-m-d', strtotime($dateTime));

                    $today = date('Y-m-d');
                    $next2Days = date('Y-m-d', strtotime('+2 days'));

                    $team1 = $sdule['t1'];
                    $teamname1 = preg_replace('/\s*\[.*?\]/', '', $team1);
                    $team2 = $sdule['t2'];
                    $teamname2 = preg_replace('/\s*\[.*?\]/', '', $team2);
                @endphp
                @if ($matchDate >= $today && $matchDate <= $next2Days)
                    <div class='col match-item'>
                        <a href="score/{{ $sdule['id'] }}" style="text-decoration: none; color:#141010;">
                            @if ($sdule['ms'] === 'fixture')
                                <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>

                                    <div class='row card-body'>
                                        @php
                                            // $seriesname = $sdule['name'];
                                            // $ser = explode(', ', $seriesname);
                                            // $new_sername = end($ser);
                                        @endphp
                                        <p class='card-text col-9' style="color: #817373">
                                            {{ $sdule['series'] }}
                                        </p>
                                        <div class="match-formate col-3">
                                            @if ($sdule['matchType'] === 'odi')
                                                <span>Odi</span>
                                            @elseif($sdule['matchType'] === 't20')
                                                <span class="t20-series">T20</span>
                                            @elseif($sdule['matchType'] === 'test')
                                                <span class="test-series">Test</span>
                                            @endif
                                        </div>
                                        <div class="col-6">
                                            {{-- @foreach ($sdule['teamInfo'] as $ser) --}}
                                            <h5 class='card-title'>
                                                @if (!empty($sdule['t1img']))
                                                    <img class="img" src="{{ $sdule['t1img'] }}" alt=""
                                                        srcset="" />
                                                @endif
                                                &nbsp;&nbsp;&nbsp;
                                                {{ $teamname1 }}
                                            </h5>
                                            {{-- <img class="img" src="{{ $livematch['t2img'] }}" alt="" /> --}}
                                            <h5 class='card-title'>
                                            @if (!empty($sdule['t2img']))
                                                <img class="img" src="{{ $sdule['t2img'] }}" alt="" />
                                            @endif
                                                &nbsp;&nbsp;&nbsp;
                                                {{ $teamname2 }}
                                            </h5>
                                            {{-- @endforeach --}}
                                        </div>
                                        <div class="col-6">
                                            <p class=' card-text text-align-right'>
                                                @php
                                                    $dateTimeGMT = new DateTime(
                                                        $sdule['dateTimeGMT'],
                                                        new DateTimeZone('GMT'),
                                                    );
                                                    $dateGMT = new DateTime($sdule['dateTimeGMT']);
                                                    $dateTimeGMT->setTimezone(new DateTimeZone('Asia/Kolkata'));
                                                    // echo $date->format('Y-m-d h:i A');
                                                    $seriesdate = strtotime($sdule['dateTimeGMT']);
                                                    echo date('M j', $seriesdate) .
                                                        ' &nbsp;' .
                                                        $dateGMT->format('h:i A') .
                                                        '(GMT)/' .
                                                        '<b>' .
                                                        $dateTimeGMT->format('h:i A') .
                                                        '</b>' .
                                                        '(Local)';
                                                @endphp
                                            </p>
                                        </div>
                                        <p class='card-text' style='color:#0c7cde'>{{ $sdule['status'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </a>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection
