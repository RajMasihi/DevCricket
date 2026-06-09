@extends('layouts.main')

@section('title', $scorecardDatainfo['appindex']['seotitle'] ?? '')

@section('main-container')
        @php
            $team1 = $scorecardDatainfo['team1']['teamname'] ?? '';
            $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1), '0'));
            $team2 = $scorecardDatainfo['team2']['teamname'] ?? '';
            $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2), '0'));
            $matchId = $scorecardDatainfo['matchid'] ?? $scorecardDatainfo['matchId'] ?? '';
            $seriesId = $scorecardDatainfo['seriesid'] ?? $scorecardDatainfo['seriesid'] ?? '';
            $seriesName = $scorecardDatainfo['seriesname'] ?? $scorecardDatainfo['seriesname'] ?? '';
            $seriesNameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $seriesName), '0'));
            $activeTab = request()->get('tab', 'informe');
            $matchState = strtolower($scorecardDatainfo['state'] ?? '');
        @endphp
        <div class="container-fluid main-section" id="cricket-matchdetail-page" data-active-tab="{{ e($activeTab) }}" data-match-state="{{ e($matchState) }}">

        <div class="d-flex pb-3">
            @php
                // Adjust for second flow: URLs without "vs" and use only team1NameSlug and team2NameSlug plain (hyphen-case), if needed.
                $matchUrlTeamSlug = $team1NameSlug . '-' . $team2NameSlug;
            @endphp
            <a href="{{ url('/score/' . $matchId . '/' . $matchUrlTeamSlug . '?tab=informe') }}"
               id="inform_btn"
               class="btn me-2 scoreboard-title{{ $activeTab == 'informe' ? ' active-tab' : '' }}"
               type="button">
                Informe
            </a>
            <a href="{{ url('/score-scoreboard/' . $matchId . '/' . $matchUrlTeamSlug . '?tab=scoreboard') }}"
               id="scoreboard_btn"
               class="btn me-2 scoreboard-title{{ $activeTab == 'scoreboard' ? ' active-tab' : '' }}"
               type="button">
                Match Scoreboard
            </a>
            <a href="{{ url('/score-player/' . $matchId . '/' . $matchUrlTeamSlug . '?tab=players') }}"
               id="players_btn"
               class="btn me-2 scoreboard-title{{ $activeTab == 'players' ? ' active-tab' : '' }}"
               type="button">
                Players
            </a>
            <a href="{{ url('/point-table/'.$seriesId.'/'.$seriesNameSlug) }}"
               class="btn me-2 scoreboard-title point-table-nav"
               type="button">
                Point Table
            </a>
            <a href="{{ url('/stats/'.$seriesId.'/'.$seriesNameSlug) }}"
               class="btn me-2 scoreboard-title"
               type="button">
                Stats
            </a>
        </div>


        <!-- Informe Section (default shown) -->
        <section id="informe" style="display:{{ $activeTab == 'informe' ? 'block' : 'none' }};">
            <div class="alert alert-info mt-4 mb-0 text-center">
                <div class="container my-4">
                    <div class="card mb-4">
                        <div class="card-header" style="background-color:#053259; color: #fff;">
                            <h3>
                                {{ $scorecardDatainfo['seriesname'] ?? '' }}
                                <small class="text-light">({{ $scorecardDatainfo['matchdesc'] ?? '' }})</small>
                            </h3>
                            <div>
                                <span class="badge bg-success">{{ $scorecardDatainfo['matchformat'] ?? '' }}</span>
                                <span class="ms-2">Status: <b>{{ $scorecardDatainfo['status'] ?? '' }}</b></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                {{-- DISPLAY TEAMS --}}
                                <div class="col-md-6 text-center border-end">
                                    <span class="h4">{{ $scorecardDatainfo['team1']['teamname'] ?? '' }}</span>
                                    <div><small>({{ $scorecardDatainfo['team1']['teamsname'] ?? '' }})</small></div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <span class="h4">{{ $scorecardDatainfo['team2']['teamname'] ?? '' }}</span>
                                    <div><small>({{ $scorecardDatainfo['team2']['teamsname'] ?? '' }})</small></div>
                                </div>
                            </div>

                            {{-- VENUE AND DATE --}}
                            <div class="mb-3">
                                <b>Venue:</b>
                                {{ ($scorecardDatainfo['venueinfo']['ground'] ?? '') . (isset($scorecardDatainfo['venueinfo']['city']) ? ', ' . $scorecardDatainfo['venueinfo']['city'] : '') }},
                                {{ $scorecardDatainfo['venueinfo']['country'] ?? '' }}
                            </div>
                            <div class="mb-3">
                                <b>Start Time:</b>
                                {{ isset($scorecardDatainfo['startdate']) ? date('d M, Y h:i A', intval($scorecardDatainfo['startdate'])/1000) : '' }}
                            </div>
                            <div class="mb-3">
                                <b>End Time:</b>
                                {{ isset($scorecardDatainfo['enddate']) ? date('d M, Y h:i A', intval($scorecardDatainfo['enddate'])/1000) : '' }}
                            </div>

                            {{-- MATCH STATE/TOSS/SHORT STATUS --}}
                            <div class="mb-3">
                                <b>Toss Status:</b> {{ $scorecardDatainfo['tossstatus'] ?? '' }} <br/>
                                <b>State:</b> {{ $scorecardDatainfo['state'] ?? '' }} <br/>
                                <b>Short Status:</b> {{ $scorecardDatainfo['shortstatus'] ?? '' }}
                            </div>

                            {{-- UMPIRES / REFEREE --}}
                            <div class="mb-3">
                                <b>Umpires:</b>
                                {{ $scorecardDatainfo['umpire1']['name'] ?? '' }}@if(!empty($scorecardDatainfo['umpire1']['country'])) ({{ $scorecardDatainfo['umpire1']['country'] }})@endif,
                                {{ $scorecardDatainfo['umpire2']['name'] ?? '' }}@if(!empty($scorecardDatainfo['umpire2']['country'])) ({{ $scorecardDatainfo['umpire2']['country'] }})@endif,
                                {{ $scorecardDatainfo['umpire3']['name'] ?? '' }}@if(!empty($scorecardDatainfo['umpire3']['country'])) ({{ $scorecardDatainfo['umpire3']['country'] }})@endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Scoreboard Section -->
        <section id="scoreboard" style="display:{{ $activeTab == 'scoreboard' ? 'block' : 'none' }};">
            <div class="row row-cols-1 row-cols-md-2 g-4 pt-2" id="scoreboard_live_container">
                @php
                    $matchStatus = $scorecardData['status'] ?? '';
                    $scoreCards = $scorecardData['scorecard'] ?? [];
                    $seriesName = $scorecardData['appindex']['seotitle'] ?? ($scorecardData['status'] ?? '');
                @endphp

                @if (!empty($seriesName) && !empty($scoreCards) && is_array($scoreCards))
                    <!-- Match Status -->
                    <div class="col-12 match-result-row mb-3 border shadow-sm rounded bg-white py-3 px-2" >
                        <div class="text-center">
                            <span class="winner-title">{{ $matchStatus }}</span>
                            @if($matchState === 'in progress')
                                <div class="small text-success mt-1">Live ball-by-ball auto update every 8 seconds</div>
                            @endif
                        </div>
                    </div>
                    <!-- Scorecards -->
                    @foreach ($scoreCards as $scIdx => $sc)
                        @php
                            $batTeam = $sc['batteamname'] ?? 'Team';
                            $totalRuns = $sc['score'] ?? '';
                            $totalWickets = $sc['wickets'] ?? '';
                            $totalOvers = $sc['overs'] ?? '';
                            $batsmen = $sc['batsman'] ?? [];
                            $bowlers = $sc['bowler'] ?? [];
                            $didNotBatRaw = $sc['didnotbat'] ?? $sc['didNotBat'] ?? $sc['yettobat'] ?? $sc['yetToBat'] ?? $sc['dnb'] ?? [];

                            // Normalize batsman payload because API key names change across matches.
                            $normalizedBatsmen = [];
                            foreach ($batsmen as $batRow) {
                                $batName = trim((string) (
                                    $batRow['name']
                                    ?? $batRow['batname']
                                    ?? $batRow['batsmanname']
                                    ?? $batRow['playername']
                                    ?? $batRow['batsman']['name']
                                    ?? ''
                                ));

                                if ($batName === '') {
                                    continue;
                                }

                                $normalizedBatsmen[] = [
                                    'name' => $batName,
                                    'outdec' => $batRow['outdec'] ?? $batRow['outDesc'] ?? $batRow['dismissal-text'] ?? $batRow['howout'] ?? '',
                                    'runs' => $batRow['runs'] ?? $batRow['r'] ?? $batRow['run'] ?? $batRow['playerrun'] ?? 0,
                                    'balls' => $batRow['balls'] ?? $batRow['b'] ?? $batRow['ball'] ?? 0,
                                    'fours' => $batRow['fours'] ?? $batRow['4s'] ?? $batRow['four'] ?? 0,
                                    'sixes' => $batRow['sixes'] ?? $batRow['6s'] ?? $batRow['six'] ?? 0,
                                    'strkrate' => $batRow['strkrate'] ?? $batRow['strikerate'] ?? $batRow['sr'] ?? 0,
                                ];
                            }
                            $batsmen = $normalizedBatsmen;

                            // Some APIs return DNB/yet-to-bat players separately; append them with zero stats.
                            $existingBatsmanNames = [];
                            foreach ($batsmen as $batRow) {
                                $batName = trim((string) ($batRow['name'] ?? ''));
                                if ($batName !== '') {
                                    $existingBatsmanNames[strtolower($batName)] = true;
                                }
                            }

                            if (!empty($didNotBatRaw) && is_array($didNotBatRaw)) {
                                foreach ($didNotBatRaw as $dnbPlayer) {
                                    $dnbName = '';
                                    if (is_string($dnbPlayer)) {
                                        $dnbName = trim($dnbPlayer);
                                    } elseif (is_array($dnbPlayer)) {
                                        $dnbName = trim((string) ($dnbPlayer['name'] ?? $dnbPlayer['batsmanname'] ?? ''));
                                    }

                                    if ($dnbName === '' || isset($existingBatsmanNames[strtolower($dnbName)])) {
                                        continue;
                                    }

                                    $batsmen[] = [
                                        'name' => $dnbName,
                                        'outdec' => 'did not bat',
                                        'runs' => 0,
                                        'balls' => 0,
                                        'fours' => 0,
                                        'sixes' => 0,
                                        'strkrate' => 0,
                                    ];
                                    $existingBatsmanNames[strtolower($dnbName)] = true;
                                }
                            }
                        @endphp

                        <div class="col d-flex">
                            <div class="scoreboard-card shadow-sm">
                                <div class="scoreboard-header">
                                    <div class="score-header-content">
                                        <div class="team-name-down">
                                            {{ $batTeam }}
                                        </div>
                                    </div>
                                    <span class="score-line">
                                        @if($totalRuns !== '' && $totalWickets !== '' && $totalOvers !== '')
                                            {{ $totalRuns }}/{{ $totalWickets }} <span class="score-line-overs">({{ $totalOvers }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="card-body px-4 py-3">
                                    <div>
                                        <h5 class="batbowl-title"><span>Batting</span></h5>
                                        <div class="table-overflow-x">
                                            <table class="summary-table">
                                                <thead>
                                                    <tr>
                                                        <th class="th1">Batsman</th>
                                                        <th>R</th>
                                                        <th>B</th>
                                                        <th>4s</th>
                                                        <th>6s</th>
                                                        <th>SR</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($batsmen as $bat)
                                                        @if(!empty($bat['runs']) || !empty($bat['balls']) || !empty($bat['fours']))
                                                        <tr class="{{ $loop->index % 2 == 0 ? 'even-row' : 'odd-row' }}">
                                                            <td>
                                                                <span class="fw-600">{{ $bat['name'] ?? '' }}</span>
                                                                @if(!empty($bat['outdec']))
                                                                    <br>
                                                                    <span class="dismissal-text">{{ $bat['outdec'] }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ is_numeric($bat['runs'] ?? null) ? $bat['runs'] : 0 }}</td>
                                                            <td>{{ is_numeric($bat['balls'] ?? null) ? $bat['balls'] : 0 }}</td>
                                                            <td>{{ is_numeric($bat['fours'] ?? null) ? $bat['fours'] : 0 }}</td>
                                                            <td>{{ is_numeric($bat['sixes'] ?? null) ? $bat['sixes'] : 0 }}</td>
                                                            <td>{{ is_numeric($bat['strkrate'] ?? null) ? $bat['strkrate'] : 0 }}</td>
                                                        </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-2">
                                            <h5 class="batbowl-title"><span>Bowling</span></h5>
                                            <div class="table-overflow-x">
                                                <table class="summary-table">
                                                    <thead>
                                                        <tr>
                                                            <th class="th1">Bowler</th>
                                                            <th>O</th>
                                                            <th>R</th>
                                                            <th>W</th>
                                                            <th>ECO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($bowlers as $bowl)
                                                            <tr class="{{ $loop->index % 2 == 0 ? 'even-row' : 'odd-row' }}">
                                                                <td><span class="fw-600">{{ $bowl['name'] ?? '' }}</span></td>
                                                                <td>{{ $bowl['overs'] ?? '0' }}</td>
                                                                <td>{{ $bowl['runs'] ?? '0' }}</td>
                                                                <td>{{ $bowl['wickets'] ?? '0' }}</td>
                                                                <td>{{ $bowl['economy'] ?? '0' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                @php
                                                    $extras = $sc['extras'] ?? [];
                                                    $extrasText = '';
                                                    $totalExtras = 0;

                                                    if (!empty($extras) && is_array($extras)) {
                                                        $extrasParts = [];
                                                        if(isset($extras['byes']) && $extras['byes'] > 0) {
                                                            $extrasParts[] = 'B: ' . $extras['byes'];
                                                        }
                                                        if(isset($extras['legbyes']) && $extras['legbyes'] > 0) {
                                                            $extrasParts[] = 'LB: ' . $extras['legbyes'];
                                                        }
                                                        if(isset($extras['wides']) && $extras['wides'] > 0) {
                                                            $extrasParts[] = 'WD: ' . $extras['wides'];
                                                        }
                                                        if(isset($extras['noballs']) && $extras['noballs'] > 0) {
                                                            $extrasParts[] = 'NB: ' . $extras['noballs'];
                                                        }
                                                        if(isset($extras['penalty']) && $extras['penalty'] > 0) {
                                                            $extrasParts[] = 'PEN: ' . $extras['penalty'];
                                                        }
                                                        if(isset($extras['total'])) {
                                                            $totalExtras = $extras['total'];
                                                        } else {
                                                            $totalExtras =
                                                                ($extras['byes'] ?? 0) +
                                                                ($extras['legbyes'] ?? 0) +
                                                                ($extras['wides'] ?? 0) +
                                                                ($extras['noballs'] ?? 0) +
                                                                ($extras['penalty'] ?? 0);
                                                        }
                                                        $extrasText = implode(', ', $extrasParts);
                                                    }
                                                @endphp

                                                @if($totalExtras > 0)
                                                    <div class="extras-info mt-2" style="font-size:0.99rem;">
                                                        <span class="fw-600" style="color:#2f5795;">Extras:</span>
                                                        <span>{{ $totalExtras }}</span>
                                                        @if(!empty($extrasText))
                                                            <span style="color:#3a4c66;">({{ $extrasText }})</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col">
                        <div class="not-started-box text-center">
                            <h3>Match Start Date/Time!</h3>
                            @php
                                // Correction for upcoming match status and local time display
                                $matchStatusShow = '';
                                $localTime = '';

                                // Try to get a match status from the most available source
                                if (!empty($scorecardData['status'])) {
                                    $matchStatusShow = $scorecardData['status'];
                                } elseif (isset($scoreCards['status']) && !empty($scoreCards['status'])) {
                                    $matchStatusShow = $scoreCards['status'];
                                } elseif (isset($match) && !empty($match['status'] ?? null)) {
                                    $matchStatusShow = $match['status'];
                                }

                                // For upcoming matches, show "Match Not Started" with local time from startDate if possible
                                if (empty($matchStatusShow) || stripos($matchStatusShow, 'not started') !== false || stripos($matchStatusShow, 'upcoming') !== false) {
                                    $matchStatusShow = 'Match Not Started';

                                    // Try to get local time from a 'startdate' field (common for upcoming matches)
                                    $rawStart = $scorecardData['startdate']
                                        ?? $scorecardDatainfo['startdate']
                                        ?? $scoreCards['startdate']
                                        ?? $match['startdate']
                                        ?? null;

                                    if (!empty($rawStart)) {
                                        // startdate is usually milliseconds, convert to int seconds if needed
                                        if (is_numeric($rawStart) && $rawStart > 100000000000) {
                                            $rawStart = intval($rawStart / 1000);
                                        } elseif (is_numeric($rawStart)) {
                                            $rawStart = intval($rawStart);
                                        } else {
                                            $rawStart = null;
                                        }
                                        if (!empty($rawStart)) {
                                            try {
                                                $dt = new DateTime("@$rawStart");
                                                $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
                                                $localTime = $dt->format('d M, Y h:i A');
                                            } catch(Exception $e) {
                                                $localTime = '';
                                            }
                                        }
                                    }
                                } else {
                                    // Try to parse GMT datetime string in status if available
                                    if (preg_match('/([A-Za-z]{3} \d{2}, \d{2}:\d{2} GMT)/', $matchStatusShow, $matches)) {
                                        try {
                                            $gmtDateTime = $matches[1];
                                            $date = DateTime::createFromFormat('M d, H:i T Y', $gmtDateTime . ' ' . date('Y'), new DateTimeZone('GMT'));
                                            if($date !== false) {
                                                $date->setTimezone(new DateTimeZone('Asia/Kolkata'));
                                                $localTime = $date->format('d M, Y h:i A');
                                            }
                                        } catch(Exception $e) {
                                            $localTime = '';
                                        }
                                    }
                                }
         
                            @endphp

                            <div class="match-status-box text-center mb-2" style="font-size:1.13rem;">
                                <span style="font-weight:600;color:#255;">
                                    {{ $matchStatusShow }} <br>
                                    Local Time : {{ $localTime }}
                                </span>
                            </div>
                            <p>Stay tuned. Match details will appear here once available.</p>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <!-- Players Section (default hidden, fill as per requirement) -->
        <section id="players" style="display:{{ $activeTab == 'players' ? 'block' : 'none' }};">
            <div class="alert alert-info mt-4 mb-0 text-center">
            @isset($errorMsg)
            <div class="alert alert-danger">{{ $errorMsg }}</div>
            @endisset

            @php
                // Helper: Render current "playing XI" (not benches or old data)
                function renderCurrentPlayingXI($playerGroups) {
                    // Accepts the direct list of groups, looks for category "playing XI", expects: [ [ 'category'=>'playing XI', 'player'=>[...]], ... ]
                    $playingXI = null;
                    foreach ($playerGroups as $group) {
                        if (
                            isset($group['category']) &&
                            strtolower(trim($group['category'])) === 'playing xi'
                            && isset($group['player']) && is_array($group['player'])
                        ) {
                            $playingXI = $group['player'];
                            break;
                        }
                    }
                    if ($playingXI && is_array($playingXI) && count($playingXI)) {
                        echo '<ul class="mb-1">';
                        foreach ($playingXI as $p) {
                            $isCaptain = !empty($p['captain']) ? ' <span style="color:#1a87ff;font-size:.95em;">(c)</span>' : '';
                            $isKeeper = !empty($p['keeper']) ? ' <span style="color:#008b8b;font-size:.95em;">(wk)</span>' : '';
                            echo '<li>'
                                 . e($p['name'] ?? 'Unnamed player')
                                 . $isCaptain
                                 . $isKeeper
                                 . (!empty($p['role']) ? ' &mdash; <span style="color:#888;">'.e($p['role']).'</span>' : '')
                                 . '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<div><em>No current playing XI players listed.</em></div>';
                    }
                }
            @endphp

            @if(
                (!empty($teamsData['team1']['team'])) ||
                (!empty($teamsData['team2']['team']))
            )
                <div class="row justify-content-center text-start mb-3">
                    @foreach(['team1', 'team2'] as $teamKey)
                        @if(!empty($teamsData[$teamKey]['team']))
                            <div class="col-md-6">
                                <div class="mb-3 p-3 rounded shadow bg-light h-100">
                                    <h5 class="mb-3 text-primary fw-bold">
                                        {{ $teamsData[$teamKey]['team']['teamname'] ?? 'Unknown Team' }}
                                        <span class="text-secondary" style="font-size:.92em;">
                                            ({{ $teamsData[$teamKey]['team']['teamsname'] ?? '' }})
                                        </span>
                                    </h5>
                                    @if(!empty($teamsData[$teamKey]['players']) && is_array($teamsData[$teamKey]['players']))
                                        <div class="mb-2">
                                            <span class="fw-semibold text-decoration-underline">Playing XI (After Toss)</span>
                                            @php
                                                $playingXI = null;
                                                foreach ($teamsData[$teamKey]['players'] as $group) {
                                                    if (
                                                        isset($group['category']) &&
                                                        strtolower(trim($group['category'])) === 'playing xi' &&
                                                        isset($group['player']) && is_array($group['player'])
                                                    ) {
                                                        $playingXI = $group['player'];
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if($playingXI && is_array($playingXI) && count($playingXI))
                                                <div class="table-responsive mt-3">
                                                    <table class="table table-bordered table-hover align-middle table-sm" style="background:#f7f9fc;">
                                                        <thead class="table-primary">
                                                            <tr>
                                                                <th style="width:48px;"></th>
                                                                <th>Name</th>
                                                                <th>Role</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($playingXI as $p)
                                                                <tr>
                                                                    <td>
                                                                        @php
                                                                            $img = $p['faceimageid'] ?? null;
                                                                            $playername = isset($p['name'])
                                                                                ? strtolower(trim(preg_replace('/[^a-z0-9]+/i', '0', $p['name']), '0'))
                                                                                : 'no-image';
                                                                            $playerImg = $img
                                                                                ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $img . '/' . $playername . '.jpg?d=low&p=gthumb'
                                                                                : null;
                                                                        @endphp
                                                                        @if($playerImg)
                                                                            <img src="{{ $playerImg }}" alt="Player Photo" class="rounded-circle" style="width:40px; height:40px; object-fit:cover; border:1.5px solid #99c;">
                                                                        @else
                                                                            <span class="d-inline-block rounded-circle bg-secondary" style="width:40px;height:40px;line-height:40px;text-align:center;color:#fff;">
                                                                                <i class="bi bi-person-fill"></i>
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">{{ $p['name'] ?? 'Unnamed player' }}</span>
                                                                        @if(!empty($p['captain']))
                                                                            <span class="badge bg-info text-dark ms-1" style="font-size:.9em;">C</span>
                                                                        @endif
                                                                        @if(!empty($p['keeper']))
                                                                            <span class="badge bg-success ms-1" style="font-size:.9em;">WK</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <span class="text-muted">{{ $p['role'] ?? '0' }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-warning py-2 px-3 my-2" style="font-size:.98em;"><em>No current playing XI players listed.</em></div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="alert alert-warning py-2 px-3 my-2" style="font-size:.98em;"><em>No players listed.</em></div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
           
                <style>
                    table tr td, table tr th {
                        vertical-align: middle !important;
                    }
                </style>
        
            @else
                <div>No team data found.</div>
            @endif
            </div>
        </section>
    </div>

    <style>
        .active-tab {
            border-bottom: 2px solid #053259 !important;
        }
    </style>
@endsection