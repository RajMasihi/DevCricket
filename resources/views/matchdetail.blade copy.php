@extends('layouts.main')
<title>@yield('title', 'Result Matchs')</title>
@section('main-container')
 <div class="container-fluit main-section">
    <h3 class="text-center">Cricket Live ScoreBoard</h3>
    <div class="row row-cols-1 row-cols-md-2 g-4">
           @foreach ($matches as $livematch)
    {{-- @if (!empty($livematch['matchEnded'] && $livematch['score'][0]['r'])) --}}
    {{-- @if ($livematch['matchEnded'] === true && !empty($livematch['score'][0]['r'])) --}}

    <div class='col match-item'>
        {{-- <a href="score/{{ $livematch['id'] }}" style="text-decoration: none; color:#141010;">
                <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                    <div class='row card-body'>

                        <p class='card-text col-8' style="color: #817373">
                            @php
                                $serise_match = explode(',', $livematch['name']);
                                $secondName = trim($serise_match[1] ?? '');
                            @endphp
                            {{ $secondName }}, {{ $livematch['venue'] ?? '' }}
                        </p>

                        <p class="match-formate col-2" style='margin-top:-15px;'>
                            @if ($livematch['matchType'] === 'odi')
                                <span>ODI</span>
                            @elseif ($livematch['matchType'] === 't20')
                                <span class="t20-series">T20</span>
                            @elseif ($livematch['matchType'] === 'test')
                                <span class="test-series">Test</span>
                            @endif
                        </p>

                        <div class="live col-2" style="color: #817373">
                            Result
                        </div>

                        TEAM SCORE LOGIC
                        @php
                            $team_score = [];

                            if (!empty($livematch['teamInfo']) || !empty($livematch['score'])) {
                                foreach ($livematch['teamInfo'] as $team) {
                                    $teamScore = collect($livematch['score'])->first(function ($s) use ($team) {
                                        $inningTeam = preg_replace('/\sInning\s\d+/i', '', $s['inning']);
                                        return trim($inningTeam) === trim($team['name']);
                                    });

                                    $team_score[] = [
                                        'team'    => $team['name'],
                                        'img'     => $team['img'],
                                        'runs'    => $teamScore['r'] ?? null,
                                        'wickets' => $teamScore['w'] ?? null,
                                        'overs'   => $teamScore['o'] ?? null,
                                    ];
                                }
                            }
                        @endphp

                        @foreach($team_score as $team)
                            <div class="col-6">
                                <h5 class="card-title">
                                    <img class="img" src="{{ $team['img'] }}" alt="" />
                                    {{ $team['team'] }}
                                </h5>
                            </div>

                            <div class="col-6 text-center">
                                @if(!is_null($team['runs']))
                                    {{ $team['runs'] }}/{{ $team['wickets'] }} ({{ $team['overs'] }} ov)
                                @endif
                            </div>
                        @endforeach

                        <p class='card-text' style='color:#de280c'>
                            {{ $livematch['status'] }}
                        </p>

                    </div>
                </div>
            </a>
        </div> --}}
         {{-- <h1>{{ $livematch['name'] }}</h1>
    <p><strong>Type:</strong> {{ $livematch['matchType'] }}</p>
    <p><strong>Status:</strong> {{ $livematch['status'] }}</p>
    <p><strong>Venue:</strong> {{ $livematch['venue'] }}</p>
    <p><strong>Date:</strong> {{ $livematch['date'] }}</p>

    <h2>Teams</h2>
    <ul>
        @foreach ($livematch['teams'] as $team)
            <li>{{ $team }}</li>
        @endforeach
    </ul>

    <h2>Score Summary</h2>
    <ul>
        @foreach ($livematch['score'] as $score)
            <li>{{ $score['inning'] }} — {{ $score['r'] }}/{{ $score['w'] }} ({{ $score['o'] }} ov)</li>
        @endforeach
    </ul>

    <h2>Batting & Bowling</h2>
    @foreach ($livematch['scorecard'] as $inning)
        <h3>{{ $inning['inning'] }}</h3>

        <h4>Batting</h4>
        <table border="1" cellpadding="5">
            <tr>
                <th>Batsman</th><th>R</th><th>B</th><th>4s</th><th>6s</th><th>SR</th>
            </tr>
            @foreach ($inning['batting'] as $bat)
            <tr>
                <td>{{ $bat['batsman']['name'] }}</td>
                <td>{{ $bat['r'] }}</td>
                <td>{{ $bat['b'] }}</td>
                <td>{{ $bat['4s'] }}</td>
                <td>{{ $bat['6s'] }}</td>
                <td>{{ $bat['sr'] }}</td>
            </tr>
            @endforeach
        </table>

        <h4>Bowling</h4>
        <table border="1" cellpadding="5">
            <tr>
                <th>Bowler</th><th>O</th><th>R</th><th>W</th><th>ECO</th>
            </tr>
            @foreach ($inning['bowling'] as $bowl)
            <tr>
                <td>{{ $bowl['bowler']['name'] }}</td>
                <td>{{ $bowl['o'] }}</td>
                <td>{{ $bowl['r'] }}</td>
                <td>{{ $bowl['w'] }}</td>
                <td>{{ $bowl['eco'] }}</td>
            </tr>
            @endforeach
        </table>
    @endforeach --}}
    {{-- @endif --}}
@endforeach

        </div>
 
 </div>
@endsection