@extends('layouts.main')

@section('title', $scorecardData['appindex']['seotitle'] ?? '')

@section('main-container')
    <div class="container-fluid main-section">
        <!-- Tab buttons -->
        @php
            $team1 = $scorecardData['team1']['teamname'] ?? '';
            $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1), '-'));
            $team2 = $scorecardData['team2']['teamname'] ?? '';
            $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2), '-'));
            $matchId = $scorecardData['matchid'] ?? $scorecardData['matchId'] ?? '';
        @endphp

        <div class="d-flex pb-3">
            <a href="#" id="inform_btn" class="btn me-2 scoreboard-title active-tab" type="button">Informe</a>
            <a href="#" id="scoreboard_btn" class="btn me-2 scoreboard-title" type="button">Match Scoreboard</a>
            <a href="#" id="players_btn" class="btn scoreboard-title" type="button">Players</a>
        </div>

        <!-- Informe Section (default shown) -->
        <section id="informe" style="display:block;">
            <div class="alert alert-info mt-4 mb-0 text-center">
                <div class="container my-4">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h3>
                                {{ $scorecardData['seriesname'] ?? '' }}
                                <small class="text-light">({{ $scorecardData['matchdesc'] ?? '' }})</small>
                            </h3>
                            <div>
                                <span class="badge bg-success">{{ $scorecardData['matchformat'] ?? '' }}</span>
                                <span class="ms-2">Status: <b>{{ $scorecardData['status'] ?? '' }}</b></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                {{-- DISPLAY TEAMS --}}
                                <div class="col-md-6 text-center border-end">
                                    <span class="h4">{{ $scorecardData['team1']['teamname'] ?? '' }}</span>
                                    <div><small>({{ $scorecardData['team1']['teamsname'] ?? '' }})</small></div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <span class="h4">{{ $scorecardData['team2']['teamname'] ?? '' }}</span>
                                    <div><small>({{ $scorecardData['team2']['teamsname'] ?? '' }})</small></div>
                                </div>
                            </div>

                            {{-- VENUE AND DATE --}}
                            <div class="mb-3">
                                <b>Venue:</b>
                                {{ ($scorecardData['venueinfo']['ground'] ?? '') . (isset($scorecardData['venueinfo']['city']) ? ', ' . $scorecardData['venueinfo']['city'] : '') }},
                                {{ $scorecardData['venueinfo']['country'] ?? '' }}
                            </div>
                            <div class="mb-3">
                                <b>Start Time:</b>
                                {{ isset($scorecardData['startdate']) ? date('d M, Y h:i A', intval($scorecardData['startdate'])/1000) : '' }}
                            </div>
                            <div class="mb-3">
                                <b>End Time:</b>
                                {{ isset($scorecardData['enddate']) ? date('d M, Y h:i A', intval($scorecardData['enddate'])/1000) : '' }}
                            </div>

                            {{-- MATCH STATE/TOSS/SHORT STATUS --}}
                            <div class="mb-3">
                                <b>Toss Status:</b> {{ $scorecardData['tossstatus'] ?? '' }} <br/>
                                <b>State:</b> {{ $scorecardData['state'] ?? '' }} <br/>
                                <b>Short Status:</b> {{ $scorecardData['shortstatus'] ?? '' }}
                            </div>

                            {{-- UMPIRES / REFEREE --}}
                            <div class="mb-3">
                                <b>Umpires:</b>
                                {{ $scorecardData['umpire1']['name'] ?? '' }}@if(!empty($scorecardData['umpire1']['country'])) ({{ $scorecardData['umpire1']['country'] }})@endif,
                                {{ $scorecardData['umpire2']['name'] ?? '' }}@if(!empty($scorecardData['umpire2']['country'])) ({{ $scorecardData['umpire2']['country'] }})@endif,
                                {{ $scorecardData['umpire3']['name'] ?? '' }}@if(!empty($scorecardData['umpire3']['country'])) ({{ $scorecardData['umpire3']['country'] }})@endif
                            </div>
                            <div class="mb-3">
                                <b>Referee:</b>
                                {{ $scorecardData['referee']['name'] ?? '' }}@if(!empty($scorecardData['referee']['country'])) ({{ $scorecardData['referee']['country'] }})@endif
                            </div>

                            {{-- BROADCAST INFO --}}
                            @if (isset($scorecardData['broadcastinfo']) && is_array($scorecardData['broadcastinfo']))
                                <div class="mb-3">
                                    <b>Broadcast Info:</b>
                                    @foreach ($scorecardData['broadcastinfo'] as $binfo)
                                        {{ implode(', ', array_map(function($broad) {
                                            return ($broad['broadcasttype'] ?? '').": ".($broad['value'] ?? '');
                                        }, $binfo['broadcaster'] ?? [])) }} ({{ $binfo['country'] ?? '' }})
                                    @endforeach
                                </div>
                            @endif

                            {{-- BOUNDARY TRACKER VALUES --}}
                            @if (isset($scorecardData['boundarytrackervalues']['boundarytrackervalue']) && is_array($scorecardData['boundarytrackervalues']['boundarytrackervalue']))
                                <div class="mb-3">
                                    <b>Boundary Tracker:</b>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($scorecardData['boundarytrackervalues']['boundarytrackervalue'] as $bt)
                                            <div>
                                                <span class="badge bg-info">{{ $bt['type'] ?? '' }}</span>
                                                @if (!empty($bt['imageurl']))
                                                    <img src="{{ $bt['imageurl'] }}" alt="{{ $bt['type'] ?? '' }}" style="width:32px;">
                                                @endif
                                                @if (!empty($bt['clickurl']))
                                                    <a href="{{ $bt['clickurl'] }}" target="_blank">More</a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- SEO Title and Web Url --}}
                            <div class="mb-3">
                                <b>SEO Title:</b> {{ $scorecardData['appindex']['seotitle'] ?? '' }} <br/>
                                <b>Details:</b>
                                @if(!empty($scorecardData['appindex']['weburl']))
                                    <a href="{{ $scorecardData['appindex']['weburl'] }}" target="_blank">{{ $scorecardData['appindex']['weburl'] }}</a>
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="scoreboard" style="display:none;">
            <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
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
                                                        <tr class="{{ $loop->index % 2 == 0 ? 'even-row' : 'odd-row' }}">
                                                            <td>
                                                                <span class="fw-600">{{ $bat['name'] ?? '' }}</span>
                                                                @if(!empty($bat['outdec']))
                                                                    <br>
                                                                    <span class="dismissal-text">{{ $bat['outdec'] }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $bat['runs'] ?? '-' }}</td>
                                                            <td>{{ $bat['balls'] ?? '-' }}</td>
                                                            <td>{{ $bat['fours'] ?? '-' }}</td>
                                                            <td>{{ $bat['sixes'] ?? '-' }}</td>
                                                            <td>{{ $bat['strkrate'] ?? '-' }}</td>
                                                        </tr>
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
                                                                <td>{{ $bowl['overs'] ?? '-' }}</td>
                                                                <td>{{ $bowl['runs'] ?? '-' }}</td>
                                                                <td>{{ $bowl['wickets'] ?? '-' }}</td>
                                                                <td>{{ $bowl['economy'] ?? '-' }}</td>
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
                                $matchStatusShow = '';
                                if (!empty($scorecardData['status'])) {
                                    $matchStatusShow = $scorecardData['status'];
                                } elseif (isset($scoreCards['status']) && !empty($scoreCards['status'])) {
                                    $matchStatusShow = $scoreCards['status'];
                                } elseif (isset($match) && !empty($match['status'] ?? null)) {
                                    $matchStatusShow = $match['status'];
                                } else {
                                    $matchStatusShow = 'Match Not Started';
                                }
                                $localTime = '';
                                if (preg_match('/([A-Za-z]{3} \d{2}, \d{2}:\d{2} GMT)/', $matchStatusShow, $matches)) {
                                    try {
                                        $gmtDateTime = $matches[1];
                                        $date = DateTime::createFromFormat('M d, H:i T Y', $gmtDateTime . ' ' . date('Y'), new DateTimeZone('GMT'));
                                        if($date !== false) {
                                            $date->setTimezone(new DateTimeZone('Asia/Kolkata'));
                                            $localTime = $date->format('h:i A');
                                        }
                                    } catch(Exception $e) {
                                        $localTime = '';
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
        <section id="players" style="display:none;">
            <div class="alert alert-info mt-4 mb-0 text-center">
                Player section (Add your players info here.)
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // URLs for navigation (change to your actual routes if needed)
            const matchId = @json($matchId);
            const team1Slug = @json($team1NameSlug);
            const team2Slug = @json($team2NameSlug);

            const urls = {
                informe: "{{ url('score') }}/" + matchId + "/" + team1Slug + "-vs-" + team2Slug,
                scoreboard: "{{ url('score-scoreboard') }}/" + matchId + "/" + team1Slug + "-vs-" + team2Slug,
                players: "{{ url('score-player') }}/" + matchId + "/" + team1Slug + "-vs-" + team2Slug,
            };

            const tabBtns = [
                { btn: document.getElementById('inform_btn'), section: 'informe', url: urls.informe },
                { btn: document.getElementById('scoreboard_btn'), section: 'scoreboard', url: urls.scoreboard },
                { btn: document.getElementById('players_btn'), section: 'players', url: urls.players }
            ];

            function showSection(sectionId) {
                document.getElementById('informe').style.display = (sectionId === 'informe') ? "block" : "none";
                document.getElementById('scoreboard').style.display = (sectionId === 'scoreboard') ? "block" : "none";
                document.getElementById('players').style.display = (sectionId === 'players') ? "block" : "none";
            }

            function setActive(btn) {
                tabBtns.forEach(tb => tb.btn.classList.remove('active-tab'));
                btn.classList.add('active-tab');
            }

            tabBtns.forEach(tb => {
                tb.btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showSection(tb.section);
                    setActive(tb.btn);
                    if(history.pushState) {
                        history.replaceState(null, '', tb.url);
                    } else {
                        window.location.hash = tb.section;
                    }
                });
            });

            // On load, show "informe"
            showSection('informe');
            setActive(document.getElementById('inform_btn'));

        });
    </script>
    <style>
        .active-tab {
            border-bottom: 2px solid #053259 !important;
        }
    </style>
@endsection