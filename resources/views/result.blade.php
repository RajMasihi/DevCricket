@extends('layouts.main')
<title>@yield('title', 'Result Matchs')</title>
@section('main-container')
    <div class="container-fluit main-section">
        <h3 class="text-center">Cricket Live Scores Data Matchs result Matchs</h3>
        <div class="row row-cols-1 row-cols-md-2 g-4 pt-3">
            @foreach ($Results as $livematch)
                {{-- @if (!empty($livematch['ms'] && $livematch['score'][0]['r'])) --}}
                @if ($livematch['ms'] === 'result')
                    <div class='col match-item'>
                        <a href="score/{{ $livematch['id'] }}" style="text-decoration: none; color:#141010;">
                            <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                                <div class='row card-body'>

                                    <p class='card-text col-6' style="color: #817373">
                                        @php
                                            // $serise_match = explode(',', $livematch['name']);
                                            // $secondName = trim($serise_match[1] ?? '');
                                        @endphp
                                        {{ $livematch['series'] ?? '' }}
                                    </p>

                                    <p class="match-formate col-3" style='margin-top:-15px;'>
                                        @if ($livematch['matchType'] === 'odi')
                                            <span>ODI</span>
                                        @elseif ($livematch['matchType'] === 't20')
                                            <span class="t20-series">T20</span>
                                        @elseif ($livematch['matchType'] === 'test')
                                            <span class="test-series">Test</span>
                                        @endif
                                    </p>
                                    <div class="live col-3" style="color: #de280c">Result
                                    </div>
                                    @php
                                        date_default_timezone_set('Asia/Kolkata');

                                        $gmtTime = new DateTime($livematch['dateTimeGMT'], new DateTimeZone('UTC'));
                                        $gmtTime->setTimezone(new DateTimeZone('Asia/Kolkata'));

                                        $matchStartTime = $gmtTime->format('Y-m-d H:i:s');
                                        $currentTime = date('Y-m-d H:i:s');
                                        // Match status
                                        $matchStarted = $currentTime >= $matchStartTime;

                                        $team1 = $livematch['t1'];
                                        $teamname1 = preg_replace('/\s*\[.*?\]/', '', $team1);
                                        $team2 = $livematch['t2'];
                                        $teamname2 = preg_replace('/\s*\[.*?\]/', '', $team2);

                                        $status = $livematch['status'];
                                        $result = explode(' won', $status);
                                        $winnerTeam = $result[0];
                                    @endphp

                                    <div class="col-7" style="margin-top: -2px">
                                        @if ($teamname1 == $winnerTeam)
                                            <h5 class="card-title">
                                            @else
                                                <p class="card-title">
                                        @endif

                                        @if (!empty($livematch['t1img']))
                                            <img class="img" src="{{ $livematch['t1img'] }}" alt="" />
                                        @endif
                                        &nbsp;&nbsp; {{ $teamname1 }}

                                        @if ($teamname1 == $winnerTeam)
                                            </h5>
                                        @else
                                            </p>
                                        @endif
                                    </div>

                                    <div class="col-5 text-center">
                                        @if (!$matchStarted)
                                            Today<br>{{ $gmtTime->format('h:i A') }}
                                        @else
                                            @if (!is_null($livematch['t1s']))
                                                {{ $livematch['t1s'] }}
                                            @endif
                                        @endif
                                    </div>

                                    <div class="col-7">
                                        @if ($teamname2 == $winnerTeam)
                                            <h5 class="card-title">
                                            @else
                                                <p class="card-title">
                                        @endif

                                        @if (!empty($livematch['t2img']))
                                            <img class="img" src="{{ $livematch['t2img'] }}" alt="" />
                                        @endif
                                        &nbsp;&nbsp; {{ $teamname2 }}

                                        @if ($teamname2 == $winnerTeam)
                                            </h5>
                                        @else
                                            </p>
                                        @endif
                                    </div>

                                    <div class="col-5 text-center">
                                        @if (!$matchStarted)
                                        @else
                                            @if (!is_null($livematch['t2s']))
                                                {{ $livematch['t2s'] }}
                                            @endif
                                        @endif
                                    </div>
                                    @if ($livematch['ms'] === 'result')
                                        <p class='card-text' style='color:#de280c'>{{ $livematch['status'] }}</p>
                                    @else
                                        <p class='card-text' style='color:#0c7cde'>{{ $livematch['status'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>


                        </a>
                    </div>
                @endif
            @endforeach

        </div>

    </div>
@endsection
