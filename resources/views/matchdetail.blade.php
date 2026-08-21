@extends('layouts.main')

@section('title', $scorecardDatainfo['appindex']['seotitle'] ?? '')

@section('main-container')
    @php
        // update code
        $team1 = $scorecardDatainfo['team1']['teamname'] ?? '';
        $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1), '0'));
        $team2 = $scorecardDatainfo['team2']['teamname'] ?? '';
        $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2), '0'));
        $matchId = $scorecardDatainfo['matchid'] ?? $scorecardDatainfo['matchId'] ?? '';
        $seriesId = $scorecardDatainfo['seriesid'] ?? $scorecardDatainfo['seriesid'] ?? '';
        $seriesName = $scorecardDatainfo['seriesname'] ?? $scorecardDatainfo['seriesname'] ?? '';
        $seriesNameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $seriesName), '0'));
        $activeTab = $tab ?? request()->get('tab', 'informe');
        $matchState = strtolower($scorecardDatainfo['state'] ?? '');
    @endphp
<div class="container-fluid main-section" id="cricket-matchdetail-page" data-active-tab="{{ e($activeTab) }}"
    data-match-state="{{ e($matchState) }}" data-match-id="{{ e($matchId) }}">


    <div class="score-nav-wrapper">
        <div class="score-nav d-flex">
            @php
            $matchUrlTeamSlug = $team1NameSlug . '-' . $team2NameSlug;
            @endphp

            <a href="{{ url('/score/' . $matchId . '/' . $matchUrlTeamSlug . '?tab=informe') }}" id="inform_btn"
                class="btn me-2 scoreboard-title{{ $activeTab == 'informe' ? ' active-tab' : '' }}">
                Informe
            </a>

            <a href="{{ url('/score/' . $matchId . '/' . $matchUrlTeamSlug . '?tab=scoreboard') }}" id="scoreboard_btn"
                class="btn me-2 scoreboard-title{{ $activeTab == 'scoreboard' ? ' active-tab' : '' }}">
                Match Scoreboard
            </a>

            <a href="{{ url('/score/' . $matchId . '/' . $matchUrlTeamSlug . '?tab=players') }}" id="players_btn"
                class="btn me-2 scoreboard-title{{ $activeTab == 'players' ? ' active-tab' : '' }}">
                Players
            </a>
            {{-- Show Point Table button only if seriesId and seriesNameSlug are set --}}
            @if(!empty($seriesId) && !empty($seriesNameSlug))
                <a href="{{ url('/point-table/' . $seriesId . '/' . $seriesNameSlug) }}"
                    class="btn me-2 scoreboard-title point-table-nav">
                    Point Table
                </a>
            @endif
            <a href="{{ url('/stats/'.$seriesId.'/'.$seriesNameSlug) }}" class="btn me-2 scoreboard-title">
                Stats
            </a>
        </div>

    </div>

        {{-- ========================================== --}}
        {{-- TAB 1: INFORMATION       --}}
        {{-- ========================================== --}}
    <!-- Informe Section (default shown) -->
    <section id="informe" style="display: {{ $activeTab === 'informe' ? 'block' : 'none' }};">
        <div class="container my-4">
            <div class="card shadow-sm mb-4">
                {{-- HEADER --}}
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2"
                    style="background-color: #053259; color: #fff;">
                    <h3 class="h5 mb-0">
                        {{ $scorecardDatainfo['seriesname'] ?? 'N/A' }}
                        @if(!empty($scorecardDatainfo['matchdesc']))
                        <small class="text-light fs-6">({{ $scorecardDatainfo['matchdesc'] }})</small>
                        @endif
                    </h3>
                    <div class="d-flex align-items-center gap-2">
                        @if(!empty($scorecardDatainfo['matchformat']))
                        <span class="badge bg-success">{{ $scorecardDatainfo['matchformat'] }}</span>
                        @endif
                        <span class="small">Status: <b
                                id="informe_match_status">{{ $scorecardDatainfo['status'] ?? 'N/A' }}</b></span>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="card-body">
                    {{-- TEAMS DISPLAY --}}
                    <div class="row align-items-center mb-4 py-2 border-bottom">
                        <div class="col-6 text-center border-end">
                            <span
                                class="h5 d-block mb-1">{{ $scorecardDatainfo['team1']['teamname'] ?? 'Team 1' }}</span>
                            <small class="text-muted">({{ $scorecardDatainfo['team1']['teamsname'] ?? 'T1' }})</small>
                        </div>
                        <div class="col-6 text-center">
                            <span
                                class="h5 d-block mb-1">{{ $scorecardDatainfo['team2']['teamname'] ?? 'Team 2' }}</span>
                            <small class="text-muted">({{ $scorecardDatainfo['team2']['teamsname'] ?? 'T2' }})</small>
                        </div>
                    </div>

                    {{-- DETAILS GRID --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Venue:</strong>
                            {{ implode(', ', array_filter([
                                    $scorecardDatainfo['venueinfo']['ground'] ?? null,
                                    $scorecardDatainfo['venueinfo']['city'] ?? null,
                                    $scorecardDatainfo['venueinfo']['country'] ?? null
                                ])) }}
                        </div>
                        <div class="col-md-6">
                            <strong>Toss Status:</strong> {{ $scorecardDatainfo['tossstatus'] ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Start Time:</strong>
                            {{ isset($scorecardDatainfo['startdate']) ? date('d M, Y h:i A', (int)($scorecardDatainfo['startdate'] / 1000)) : 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>End Time:</strong>
                            {{ isset($scorecardDatainfo['enddate']) ? date('d M, Y h:i A', (int)($scorecardDatainfo['enddate'] / 1000)) : 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>State / Short Status:</strong>
                            {{ $scorecardDatainfo['state'] ?? 'N/A' }}
                            {{ isset($scorecardDatainfo['shortstatus']) ? "({$scorecardDatainfo['shortstatus']})" : '' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Umpires:</strong>
                            @php
                            $umpires = array_filter([
                            isset($scorecardDatainfo['umpire1']['name']) ? $scorecardDatainfo['umpire1']['name'] .
                            (!empty($scorecardDatainfo['umpire1']['country']) ? ' (' .
                            $scorecardDatainfo['umpire1']['country'] . ')' : '') : null,
                            isset($scorecardDatainfo['umpire2']['name']) ? $scorecardDatainfo['umpire2']['name'] .
                            (!empty($scorecardDatainfo['umpire2']['country']) ? ' (' .
                            $scorecardDatainfo['umpire2']['country'] . ')' : '') : null,
                            isset($scorecardDatainfo['umpire3']['name']) ? $scorecardDatainfo['umpire3']['name'] .
                            (!empty($scorecardDatainfo['umpire3']['country']) ? ' (' .
                            $scorecardDatainfo['umpire3']['country'] . ')' : '') : null,
                            ]);
                            @endphp
                            {{ !empty($umpires) ? implode(', ', $umpires) : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scoreboard Section -->

    {{-- Master Container --}}
    <div class="container-fluid py-2" id="cricket_match_wrapper">

        {{-- ========================================== --}}
        {{-- TAB 1: SCOREBOARD & LIVE COMMENTARY        --}}
        {{-- ========================================== --}}
        
        <section id="scoreboard" class="{{ $activeTab == 'scoreboard' ? '' : 'd-none-dynamic' }}">
            <div class="row pt-2" id="scoreboard_live_container">
                @php
                    $matchStatus = $scorecardData['status'] ?? '';
                    $scoreCards = $scorecardData['scorecard'] ?? [];
                    $seriesName = $scorecardData['appindex']['seotitle'] ?? ($scorecardData['status'] ?? '');
                    $matchState = $matchState ?? '';
                @endphp

                @if (!empty($seriesName) && !empty($scoreCards) && is_array($scoreCards))
                    <!-- Match Status Banner -->
                    <div class="col-12 match-result-row mb-3 border shadow-sm rounded bg-white py-3 px-3" id="match_status_row">
                        <div class="text-center">
                            <span class="winner-title fw-bold fs-5" id="match_status_text">{{ $matchStatus }}</span>
                            
                        </div>
                    </div>

                    

                    <!-- Team Filter Toggle Buttons (Responsive Wrapping Fix) -->
                    @if(count($scoreCards) > 1)
                        <div class="col-12 mb-3">
                            <div class="d-flex flex-wrap justify-content-left gap-2" role="group" aria-label="Team Scorecard Selector">
                                @foreach($scoreCards as $scIdx => $sc)
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary fw-bold sc-team-btn text-truncate btn-team-sc {{ $loop->first ? 'active' : '' }}"
                                        onclick="showScorecardTeam({{ $scIdx }}, this)">
                                        {{ $sc['batteamname'] ?? 'Innings '.($scIdx + 1) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Innings Scorecards -->
                    @foreach ($scoreCards as $scIdx => $sc)
                        @php
                            $batTeam = $sc['batteamname'] ?? 'Team';
                            $totalRuns = $sc['score'] ?? '';
                            $totalWickets = $sc['wickets'] ?? '';
                            $totalOvers = $sc['overs'] ?? '';
                            $batsmen = $sc['batsman'] ?? [];
                            $bowlers = $sc['bowler'] ?? [];
                            $didNotBatRaw = $sc['didnotbat'] ?? $sc['didNotBat'] ?? $sc['yettobat'] ?? $sc['yetToBat'] ?? $sc['dnb'] ?? [];

                            $normalizedBatsmen = [];
                            foreach (is_array($batsmen) ? $batsmen : [] as $batRow) {
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

                        <div class="col-12 sc-team-card-wrapper {{ $loop->first ? '' : 'd-none-dynamic' }} scoreboard-main" id="sc_team_card_{{ $scIdx }}">
                            <div class="scoreboard-card shadow-sm bg-white w-100 mb-3">
                                <div class="scoreboard-header p-2 p-md-3 text-white d-flex justify-content-between bg-custom-navy">
                                    <div class="team-name-down fw-bold fs-6 fs-md-5 text-truncate me-2">
                                        {{ $batTeam }}
                                    </div>
                                    <span class="score-line fw-bold fs-6 fs-md-5 text-nowrap">
                                        @if($totalRuns !== '' && $totalWickets !== '' && $totalOvers !== '')
                                            {{ $totalRuns }}/{{ $totalWickets }} <span class="score-line-overs small opacity-75">({{ $totalOvers }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="card-body p-1 p-md-3">
                                    {{-- Batting Section --}}
                                    <div class="mb-4">
                                        <h5 class="batbowl-title border-bottom pb-2 mb-2 fw-bold text-primary px-1">
                                            <span>Batting</span>
                                        </h5>

                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle table-sm cricket-score-table w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="col-player text-start">Batter</th>
                                                        <th class="col-stat text-center">R</th>
                                                        <th class="col-stat text-center">B</th>
                                                        <th class="col-stat text-center">4s</th>
                                                        <th class="col-stat text-center">6s</th>
                                                        <th class="col-stat text-center">SR</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach ($batsmen as $bat)
                                                        @if(!empty($bat['runs']) || !empty($bat['balls']) || !empty($bat['fours']))
                                                            <tr class="{{ $loop->index % 2 == 0 ? 'bg-white' : 'bg-light' }}">
                                                                {{-- Player Name & Dismissal --}}
                                                                <td class="col-player text-start py-2 px-1">
                                                                    <div class="fw-bold text-primary player-name lh-sm">
                                                                        {{ $bat['name'] ?? '' }}
                                                                    </div>
                                                                    @if(!empty($bat['outdec']))
                                                                        <div class="dismissal-text  small lh-sm">
                                                                            {{ $bat['outdec'] }}
                                                                        </div>
                                                                    @else
                                                                        <div class="dismissal-text text-muted small lh-sm">
                                                                            batting
                                                                        </div>
                                                                    @endif
                                                                </td>

                                                                {{-- Stats --}}
                                                                <td class="col-stat text-center fw-bold text-dark px-1 py-2">
                                                                    {{ is_numeric($bat['runs'] ?? null) ? $bat['runs'] : 0 }}
                                                                </td>
                                                                <td class="col-stat text-center text-secondary px-1 py-2">
                                                                    {{ is_numeric($bat['balls'] ?? null) ? $bat['balls'] : 0 }}
                                                                </td>
                                                                <td class="col-stat text-center text-secondary px-1 py-2">
                                                                    {{ is_numeric($bat['fours'] ?? null) ? $bat['fours'] : 0 }}
                                                                </td>
                                                                <td class="col-stat text-center text-secondary px-1 py-2">
                                                                    {{ is_numeric($bat['sixes'] ?? null) ? $bat['sixes'] : 0 }}
                                                                </td>
                                                                <td class="col-stat text-center text-secondary px-1 py-2">
                                                                    {{ is_numeric($bat['strkrate'] ?? null) ? $bat['strkrate'] : 0 }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Bowling Section --}}
                                    <div class="mt-3">
                                        <h5 class="batbowl-title border-bottom pb-2 mb-2 fw-bold text-primary px-1">
                                            <span>Bowling</span>
                                        </h5>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle table-sm cricket-score-table w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="col-player text-start">Bowler</th>
                                                        <th class="col-stat text-center">O</th>
                                                        <th class="col-stat text-center">R</th>
                                                        <th class="col-stat text-center">W</th>
                                                        <th class="col-stat text-center">ECO</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach (is_array($bowlers) ? $bowlers : [] as $bowl)
                                                        <tr class="{{ $loop->index % 2 == 0 ? 'bg-white' : 'bg-light' }}">
                                                            <td class="col-player text-start py-2 px-1">
                                                                <div class="fw-bold text-primary player-name lh-sm">
                                                                    {{ $bowl['name'] ?? '' }}
                                                                </div>
                                                            </td>
                                                            <td class="col-stat text-center text-secondary px-1 py-2">
                                                                {{ $bowl['overs'] ?? '0' }}
                                                            </td>
                                                            <td class="col-stat text-center text-secondary px-1 py-2">
                                                                {{ $bowl['runs'] ?? '0' }}
                                                            </td>
                                                            <td class="col-stat text-center fw-bold text-danger px-1 py-2">
                                                                {{ $bowl['wickets'] ?? '0' }}
                                                            </td>
                                                            <td class="col-stat text-center text-secondary px-1 py-2">
                                                                {{ $bowl['economy'] ?? '0' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Extras Summary --}}
                                        @php
                                            $extras = $sc['extras'] ?? [];
                                            $extrasParts = [];
                                            if (!empty($extras) && is_array($extras)) {
                                                if(!empty($extras['byes'])) $extrasParts[] = 'B ' . $extras['byes'];
                                                if(!empty($extras['legbyes'])) $extrasParts[] = 'LB ' . $extras['legbyes'];
                                                if(!empty($extras['wides'])) $extrasParts[] = 'WD ' . $extras['wides'];
                                                if(!empty($extras['noballs'])) $extrasParts[] = 'NB ' . $extras['noballs'];
                                                if(!empty($extras['penalty'])) $extrasParts[] = 'P ' . $extras['penalty'];
                                            }
                                            $totalExtras = $extras['total'] ?? array_sum(array_intersect_key($extras, array_flip(['byes', 'legbyes', 'wides', 'noballs', 'penalty'])));
                                        @endphp

                                        @if($totalExtras > 0)
                                            <div class="extras-info mt-2 p-2 bg-light border rounded text-dark small">
                                                <span class="fw-bold text-primary">Extras:</span>
                                                <span class="fw-bold me-1">{{ $totalExtras }}</span>
                                                @if(count($extrasParts) > 0)
                                                    <span class="text-muted">({{ implode(', ', $extrasParts) }})</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Live Commentary Container -->
                     @if(!empty($commentary))
                    <div class="col-12 mb-3 {{ $matchState === 'in progress' ? '' : 'd-none-dynamic' }}" id="commentary_live_container">
                        <div class="card shadow-sm border">
                            <div class="card-header text-white bg-custom-navy">
                                <strong>Ball-by-Ball Commentary</strong>
                            </div>
                            <div class="card-body p-2 commentary-scroll-box" id="commentary_list">
                                @if(!empty($commentary))
                                    @php
                                        $commList = $commentary['commentaryList'] ?? $commentary['commentary'] ?? [];
                                    @endphp
                                    @foreach(array_slice(is_array($commList) ? $commList : [], 0, 15) as $comm)
                                        <div class="border-bottom py-1">
                                            <span class="text-muted fw-bold">{{ $comm['over'] ?? '' }}</span>
                                            {{ $comm['commText'] ?? $comm['text'] ?? '' }}
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-muted p-2">Commentary will appear when the match is live.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
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

        {{-- ========================================== --}}
        {{-- TAB 2: SQUAD / PLAYING XI                  --}}
        {{-- ========================================== --}}
        <section id="players" style="display:{{ $activeTab == 'players' ? 'block' : 'none' }};">
            @isset($errorMsg)
            <div class="alert alert-danger mt-3 mb-3">{{ $errorMsg }}</div>
            @endisset

            @if (!empty($teamsData['team1']['team']) || !empty($teamsData['team2']['team']))
            <!-- Team Toggle Buttons for Squad -->
                <div class="row pt-2 mb-3">
                    <div class="col-12">
                        <div class="btn-group shadow-sm" role="group" aria-label="Squad Team Selector">
                            @if(!empty($teamsData['team1']['team']))
                            <button type="button" class="btn btn-outline-primary fw-bold squad-team-btn active"
                                onclick="showSquadTeam('team1', this)">
                                {{ $teamsData['team1']['team']['teamname'] ?? 'Team 1' }}
                            </button>
                            @endif
                            @if(!empty($teamsData['team2']['team']))
                            <button type="button"
                                class="btn btn-outline-primary fw-bold squad-team-btn {{ empty($teamsData['team1']['team']) ? 'active' : '' }}"
                                onclick="showSquadTeam('team2', this)">
                                {{ $teamsData['team2']['team']['teamname'] ?? 'Team 2' }}
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Squad Content Container -->
                <div class="row justify-content-center text-start mb-3">
                    @foreach (['team1', 'team2'] as $teamKey)
                        @if (!empty($teamsData[$teamKey]['team']))
                        <div class="col-12 squad-team-wrapper" id="squad_{{ $teamKey }}"
                            style="{{ ($loop->first) ? 'display:block;' : 'display:none;' }}">
                            <div class="p-3 rounded shadow-sm bg-white border h-100">
                                <h5 class="mb-3 text-primary fw-bold border-bottom pb-2">
                                    {{ $teamsData[$teamKey]['team']['teamname'] ?? 'Unknown Team' }}
                                    @if(!empty($teamsData[$teamKey]['team']['teamsname']))
                                    <span class="text-secondary fw-normal fs-6">
                                        ({{ $teamsData[$teamKey]['team']['teamsname'] }})
                                    </span>
                                    @endif
                                </h5>

                                @if (!empty($teamsData[$teamKey]['players']) && is_array($teamsData[$teamKey]['players']))
                                    @php
                                        $playingXI = null;
                                        $Squad =null;
                                        foreach ($teamsData[$teamKey]['players'] as $group) {
                                            if (
                                            isset($group['category']) &&
                                            strtolower(trim($group['category'])) === 'playing xi' &&
                                            !empty($group['player']) &&
                                            is_array($group['player'])
                                            ) {
                                            $playingXI = $group['player'];
                                            break;
                                            }elseif (
                                               isset($group['category']) &&
                                            strtolower(trim($group['category'])) === 'squad' &&
                                            !empty($group['player']) &&
                                            is_array($group['player'])
                                            ) {
                                            $Squad = $group['player'];
                                            break;
                                            } 
                                            
                                        }
                                    @endphp

                                <div class="mb-2">
                                    @if ($Squad && count($Squad) > 0)
                                    <span class="fw-semibold text-decoration-underline">Playing Teams</span>
                                    <div class="w-100 mt-3">
                                        <table
                                            class="table table-bordered table-hover align-middle table-sm bg-white mb-0 w-100">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th style="width: 48px;"></th>
                                                    <th>Name</th>
                                                    <th>Role</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($Squad as $p)
                                                @php
                                                $img = $p['faceimageid'] ?? null;
                                                $pNameRaw = $p['name'] ?? '';
                                                $playerSlug = !empty($pNameRaw)
                                                ? strtolower(trim(preg_replace('/[^a-z0-9]+/i', '0', $pNameRaw), '0'))
                                                : 'no-image';

                                                $Squad = $img
                                                ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $img . '/' . $playerSlug .
                                                '.jpg?d=low&p=gthumb'
                                                : null;
                                                @endphp
                                                <tr>
                                                    <td class="text-center">
                                                        @if ($Squad)
                                                        <img src="{{ $Squad }}" alt="{{ $pNameRaw }}" class="rounded-circle"
                                                            style="width: 40px; height: 40px; object-fit: cover; border: 1.5px solid #99c;">
                                                        @else
                                                        <span
                                                            class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="bi bi-person-fill"></i>
                                                        </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-break">
                                                        <span
                                                            class="fw-semibold text-dark">{{ $pNameRaw ?: 'Unnamed player' }}</span>
                                                        @if (!empty($p['captain']))
                                                        <span class="badge bg-info text-dark ms-1"
                                                            style="font-size: .8em;">C</span>
                                                        @endif
                                                        @if (!empty($p['keeper']))
                                                        <span class="badge bg-success ms-1" style="font-size: .8em;">WK</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">{{ $p['role'] ?? 'N/A' }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    @elseif ($playingXI && count($playingXI) > 0)
                                    <span class="fw-semibold text-decoration-underline">Playing Teams</span>
                                    <div class="w-100 mt-3">
                                        <table
                                            class="table table-bordered table-hover align-middle table-sm bg-white mb-0 w-100">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th style="width: 48px;"></th>
                                                    <th>Name</th>
                                                    <th>Role</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($playingXI as $p)
                                                @php
                                                $img = $p['faceimageid'] ?? null;
                                                $pNameRaw = $p['name'] ?? '';
                                                $playerSlug = !empty($pNameRaw)
                                                ? strtolower(trim(preg_replace('/[^a-z0-9]+/i', '0', $pNameRaw), '0'))
                                                : 'no-image';

                                                $playerImg = $img
                                                ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $img . '/' . $playerSlug .
                                                '.jpg?d=low&p=gthumb'
                                                : null;
                                                @endphp
                                                <tr>
                                                    <td class="text-center">
                                                        @if ($playerImg)
                                                        <img src="{{ $playerImg }}" alt="{{ $pNameRaw }}" class="rounded-circle"
                                                            style="width: 40px; height: 40px; object-fit: cover; border: 1.5px solid #99c;">
                                                        @else
                                                        <span
                                                            class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white"
                                                            style="width: 40px; height: 40px;">
                                                            <i class="bi bi-person-fill"></i>
                                                        </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-break">
                                                        <span
                                                            class="fw-semibold text-dark">{{ $pNameRaw ?: 'Unnamed player' }}</span>
                                                        @if (!empty($p['captain']))
                                                        <span class="badge bg-info text-dark ms-1"
                                                            style="font-size: .8em;">C</span>
                                                        @endif
                                                        @if (!empty($p['keeper']))
                                                        <span class="badge bg-success ms-1" style="font-size: .8em;">WK</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">{{ $p['role'] ?? 'N/A' }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-warning py-2 px-3 my-2" style="font-size: .95em;">
                                        <em>No current playing XI players listed.</em>
                                    </div>
                                    @endif
                                </div>
                                @else
                                <div class="alert alert-warning py-2 px-3 my-2" style="font-size: .95em;">
                                    <em>No players listed.</em>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="alert alert-secondary text-center my-4">No team data found.</div>
            @endif
        </section>

    </div>

    @endsection