@extends('layouts.main')
<title>@yield('title', $matches['name'] ?? '')</title>
@section('main-container')
    <div class="container-fluit main-section">
        <h3 class="text-center mb-4" style="font-weight:600;color:#053259;">Cricket Live ScoreBoard</h3>
        <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
            @if (!empty($matches['name']))
                @php
                    $status = $matches['status'] ?? '';
                    // Detect winning team name
                    $wonName = '';
                    if (
                        isset($matches['winner']['name']) &&
                        trim($matches['winner']['name']) !== ''
                    ) {
                        $wonName = $matches['winner']['name'];
                    } else {
                        // Try to parse from status
                        if (stripos($status, 'won by') !== false) {
                            $spl = explode('won by', $status);
                            $wonName = trim(str_replace('won', '', $spl[0]));
                        }
                    }
                    // Find team index of winner
                    $winnerIndex = null;
                    if (
                        isset($matches['teamInfo'][0]['name']) &&
                        $wonName &&
                        strtolower($matches['teamInfo'][0]['name']) == strtolower($wonName)
                    ) {
                        $winnerIndex = 0;
                    } elseif (
                        isset($matches['teamInfo'][1]['name']) &&
                        $wonName &&
                        strtolower($matches['teamInfo'][1]['name']) == strtolower($wonName)
                    ) {
                        $winnerIndex = 1;
                    }

                    // Map team info by team name for easy lookup
                    $teamMap = [];
                    if (isset($matches['teamInfo']) && is_array($matches['teamInfo'])) {
                        foreach ($matches['teamInfo'] as $t) {
                            if (!empty($t['name'])) {
                                $teamMap[trim(strtolower($t['name']))] = $t;
                            }
                        }
                    }
                    
                    // Build displayed innings data, ensuring unique team/innings shown and correct mapping to images/names
                    $teamInningsSeen = [];
                    $displayInnings = [];
                    if (!empty($matches['score']) && is_array($matches['score'])) {
                        foreach ($matches['score'] as $idx => $score) {
                            if (empty($score['inning'])) continue;
                            // Parse team from "TEAM_NAME Inning..." or similar
                            $inningName = $score['inning'];
                            $teamNameDisplay = explode(' Inning', $inningName)[0];
                            $teamKey = trim(strtolower($teamNameDisplay));
                            if (isset($teamInningsSeen[$teamKey])) continue; // Don't duplicate
                            $teamInfo = $teamMap[$teamKey] ?? null; // Find team info by name in inning
                            // fallback if not found for flipping innings
                            if (!$teamInfo && isset($matches['teamInfo'][$idx])) {
                                $teamInfo = $matches['teamInfo'][$idx];
                            }
                            $displayInnings[] = [
                                'teamKey'      => $teamKey,
                                'teamDisplay'  => $teamNameDisplay,
                                'teamInfo'     => $teamInfo,
                                'scoreIdx'     => $idx,
                                'altIdx'       => $idx + 2, // for test matches
                                'inningName'   => $inningName,
                                'score'        => $score,
                            ];
                            $teamInningsSeen[$teamKey] = true;
                        }
                    }
                @endphp

                {{-- WINNER SECTION --}}
                @if ($wonName && !empty($status) && $winnerIndex !== null)
                <div class="col-12 match-result-row">
                    <div class="text-center">
                        <span>
                            <img class="winner-img" src="{{ $matches['teamInfo'][$winnerIndex]['img'] ?? asset('images/trophy.png') }}" alt="Winner" />
                        </span>
                        <span class="winner-title">{{ $status }}</span>
                    </div>
                </div>
                @endif

                {{-- Iterate unique/actual innings (each team, first entry) --}}
                @foreach($displayInnings as $dInning)
                    @php
                        $teamInfo = $dInning['teamInfo'];
                        $teamDisplay = $dInning['teamDisplay'];
                        $scoreIndex = $dInning['scoreIdx'];
                        $altScoreIndex = $dInning['altIdx'];
                        $teamName = $teamInfo['name'] ?? $teamDisplay;
                        $teamIsWinner = $wonName && strtolower($teamName) == strtolower($wonName);
                        $cardWinnerClass = $teamIsWinner ? 'winner-highlight-card' : '';
                        $teamImg = $teamInfo['img'] ?? asset('images/team.png');
                        $totalRuns = $matches['score'][$scoreIndex]['r'] ?? '';
                        $totalWickets = $matches['score'][$scoreIndex]['w'] ?? '';
                        $totalOvers = $matches['score'][$scoreIndex]['o'] ?? '';
                        $altRuns = $matches['score'][$altScoreIndex]['r'] ?? '';
                        $altWickets = $matches['score'][$altScoreIndex]['w'] ?? '';
                        $altOvers = $matches['score'][$altScoreIndex]['o'] ?? '';
                    @endphp
                    <div class="col">
                        <div class="card scoreboard-card {{ $cardWinnerClass }}">
                            <div class="scoreboard-header {{ $teamIsWinner ? 'winner-row' : '' }}">
                                <div class="score-header-content">
                                    <img class="team-logo" src="{{ $teamImg }}" alt="{{ $teamDisplay }}">
                                    <div class="team-name-down">
                                        <span class="{{ $teamIsWinner ? 'team-won' : '' }}">{{ $teamDisplay }}</span>
                                    </div>
                                </div>
                                <span class="score-line">
                                    @if($totalRuns !== '' && $totalWickets !== '' && $totalOvers !== '')
                                        {{ $totalRuns }}/{{ $totalWickets }} ({{ $totalOvers }})
                                    @elseif($altRuns !== '' && $altWickets !== '' && $altOvers !== '')
                                        {{ $altRuns }}/{{ $altWickets }} ({{ $altOvers }})
                                    @endif
                                </span>
                            </div>
                            <div class="card-body" style="padding: 16px 20px;">
                                <div>
                                    <h5 style="font-weight:600; color:#225b7c; margin-bottom:10px;margin-top:0;">Batting</h5>
                                    <table class="summary-table" border="0">
                                        <tr>
                                            <th class="th1">Batsman Name</th>
                                            <th>R</th>
                                            <th>B</th>
                                            <th>4s</th>
                                            <th>6s</th>
                                            <th>SR</th>
                                        </tr>
                                        @foreach (($matches['scorecard'][$scoreIndex]['batting'] ?? []) as $bat)
                                            <tr>
                                                <td>
                                                    {{ $bat['batsman']['name'] ?? '' }}
                                                    @if(!empty($bat['dismissal-text']))
                                                        <br>
                                                        <span class="dismissal-text">{{ $bat['dismissal-text'] }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $bat['r'] ?? '' }}</td>
                                                <td>{{ $bat['b'] ?? '' }}</td>
                                                <td>{{ $bat['4s'] ?? '' }}</td>
                                                <td>{{ $bat['6s'] ?? '' }}</td>
                                                <td>{{ $bat['sr'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                    <div style="margin-top:18px;">
                                        <h5 style="font-weight:600; color:#225b7c;margin-bottom:10px;margin-top:0;">Bowling</h5>
                                        <table class="summary-table" border="0">
                                            <tr>
                                                <th class="th1">Bowler</th>
                                                <th>O</th>
                                                <th>R</th>
                                                <th>W</th>
                                                <th>ECO</th>
                                            </tr>
                                            @foreach (($matches['scorecard'][$scoreIndex]['bowling'] ?? []) as $bowl)
                                                <tr>
                                                    <td>{{ $bowl['bowler']['name'] ?? '' }}</td>
                                                    <td>{{ $bowl['o'] ?? '' }}</td>
                                                    <td>{{ $bowl['r'] ?? '' }}</td>
                                                    <td>{{ $bowl['w'] ?? '' }}</td>
                                                    <td>{{ $bowl['eco'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                    @if (!empty($matches['score'][$altScoreIndex]['inning']))
                                    <div class="mt-4">
                                        <h6 style="font-size:1.08em;color:#275e82;font-weight:600;margin-bottom:0.75em;">{{ $matches['score'][$altScoreIndex]['inning'] }}</h6>
                                        <h6 style="font-size:1em;color:#377c92;margin-bottom:0.5em;font-weight:500;">Batting</h6>
                                        <table class="summary-table" border="0">
                                            <tr>
                                                <th class="th1">Batsman</th>
                                                <th>R</th>
                                                <th>B</th>
                                                <th>4s</th>
                                                <th>6s</th>
                                                <th>SR</th>
                                            </tr>
                                            @foreach (($matches['scorecard'][$altScoreIndex]['batting'] ?? []) as $bat)
                                                <tr>
                                                    <td>{{ $bat['batsman']['name'] ?? '' }}</td>
                                                    <td>{{ $bat['r'] ?? '' }}</td>
                                                    <td>{{ $bat['b'] ?? '' }}</td>
                                                    <td>{{ $bat['4s'] ?? '' }}</td>
                                                    <td>{{ $bat['6s'] ?? '' }}</td>
                                                    <td>{{ $bat['sr'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                        <h6 style="font-size:1em;color:#377c92;margin-bottom:0.5em;font-weight:500;">Bowling</h6>
                                        <table class="summary-table" border="0">
                                            <tr>
                                                <th class="th1">Bowler</th>
                                                <th>O</th>
                                                <th>R</th>
                                                <th>W</th>
                                                <th>ECO</th>
                                            </tr>
                                            @foreach (($matches['scorecard'][$altScoreIndex]['bowling'] ?? []) as $bowl)
                                                <tr>
                                                    <td>{{ $bowl['bowler']['name'] ?? '' }}</td>
                                                    <td>{{ $bowl['o'] ?? '' }}</td>
                                                    <td>{{ $bowl['r'] ?? '' }}</td>
                                                    <td>{{ $bowl['w'] ?? '' }}</td>
                                                    <td>{{ $bowl['eco'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            @else
            <div class="col">
                <div class="not-started-box text-center">
                    <h3 style="color: #bb3636;">Match Not Started!</h3>
                    <img src="{{ asset('images/cricket_ball.png') }}" style="width:60px;margin-top:18px;margin-bottom:4px;" alt="Cricket ball" />
                    <p style="color:#60677f;font-size:1.07em;letter-spacing:.02em;">Stay tuned. Match details will appear here once available.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
