@extends('layouts.main')

@section('title', 'CricketLiveScore')

@section('main-container')
    <style>
        .active-tab {
            border-bottom: 2px solid #053259 !important;
        }
    </style>
    @php
        $activeTab = request()->get('tab', 'live');
        if (!in_array($activeTab, ['live', 'result', 'upcoming'])) {
            $activeTab = 'live';
        }
    @endphp
    <div class="container-fluid main-section" id="cricket-index-page" data-active-tab="{{ e($activeTab) }}">
        <h4 class="text-center mb-3">Cricket Live Scores</h4>
        <div class="d-flex pb-3">
            <a href="{{ url('/?tab=live') }}"
               id="live_tab_btn"
               class="btn me-2 scoreboard-title{{ $activeTab == 'live' ? ' active-tab' : '' }}"
               type="button">
                Live Score match
            </a>
            <a href="{{ url('/result?tab=result') }}"
               id="result_tab_btn"
               class="btn me-2 scoreboard-title{{ $activeTab == 'result' ? ' active-tab' : '' }}"
               type="button">
                Result
            </a>
            <a href="{{ url('/upcoming?tab=upcoming') }}"
               id="upcoming_tab_btn"
               class="btn me-2 scoreboard-title{{ $activeTab == 'upcoming' ? ' active-tab' : '' }}"
               type="button">
                Upcoming
            </a>
        </div>

        <div id="tab-sections">
            <!-- Live Tab Section -->
            <section id="live_section" style="display:{{ $activeTab == 'live' ? 'block' : 'none' }};">
                <div class="row row-cols-1 row-cols-md-2 g-4 pt-2" id="live_matches_container">
                    @if(isset($error) && $activeTab == 'live')
                        <span style="color:red;">{{ $error }}</span>
                    @elseif(isset($matches) && count($matches) === 0 && $activeTab == 'live')
                        <p>No recent matches found.</p>
                    @elseif(isset($matches) && $activeTab == 'live')
                        @php
                            usort($matches, function($a, $b) {
                                $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] : (isset($a['startDate']) ? $a['startDate'] : 0);
                                $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] : (isset($b['startDate']) ? $b['startDate'] : 0);
                                return $bDate <=> $aDate;
                            });
                        @endphp
                        @foreach($matches as $match)
                            @php
                                $mi = (isset($match['matchInfo']) && is_array($match['matchInfo'])) ? $match['matchInfo'] : [];
                                $score = (isset($match['matchScore']) && is_array($match['matchScore'])) ? $match['matchScore'] : [];
                                $team1 = $mi['team1'] ?? [];
                                $team2 = $mi['team2'] ?? [];
                                $match_id = $mi['matchId'] ?? ($match['matchId'] ?? '');
                                $team1Name = $team1['teamName'] ?? 'Team 1';
                                $team2Name = $team2['teamName'] ?? 'Team 2';
                                $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
                                $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
                                $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg' : '';
                                $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg' : '';
                                $startDate = isset($mi['startDate']) ? ((int)$mi['startDate'] / 1000) : null;
                                $localTime = $startDate ? date('d M, Y h:i A', $startDate) : '';
                                $matchDateShort = $startDate ? date('d M', $startDate) : '';
                                $matchFormat = strtoupper($mi['matchFormat'] ?? '');
                                $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' : ($matchFormat === 'TEST' ? 'match-format-test' : ''));
                                $state = strtolower($mi['state'] ?? '');
                                $status = $mi['status'] ?? '';
                                $matchDesc = $mi['matchDesc'] ?? 'Match';
                                $seriesName = $mi['seriesName'] ?? '';
                                $t1Score = $score['team1Score']['inngs1'] ?? [];
                                $t1Score2 = $score['team1Score']['inngs2'] ?? [];
                                $t2Score = $score['team2Score']['inngs1'] ?? [];
                                $t2Score2 = $score['team2Score']['inngs2'] ?? [];
                                $winnerId = $score['winningTeamId'] ?? '';
                                $team1Id = $team1['teamId'] ?? '';
                                $team2Id = $team2['teamId'] ?? '';
                                $winnerName = '';
                                $team1Won = false; $team2Won = false;
                                if (!empty($status) && preg_match('/^(.*?)\swon\b/i', $status, $m)) {
                                    $winnerName = trim($m[1]);
                                }
                                if (strcasecmp($winnerName, $team1Name) == 0) { $team1Won = true; $team2Won = false; }
                                elseif (strcasecmp($winnerName, $team2Name) == 0) { $team1Won = false; $team2Won = true; }
                                elseif(!empty($winnerId)) {
                                    $team1Won = ($winnerId && ($winnerId == $team1Id)); $team2Won = ($winnerId && ($winnerId == $team2Id));
                                }
                                $team1CssClass = $team1Won ? 'winner-team' : 'loser-team';
                                $team2CssClass = $team2Won ? 'winner-team' : 'loser-team';
                            @endphp
                            <div class="col match-item">
                                <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}" style="text-decoration: none; color:#141010;">
                                    <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                                        <div class="row card-body">
                                            <div class="col-6">
                                                <p class="card-text mb-0" style="color: #817373; font-size:14px">
                                                    <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                                </p>
                                                @if(!empty($matchDateShort))
                                                    <p class="card-text mb-0 mt-1" style="color: #5d6570; font-size:12px;">
                                                        Match Date: {{ $matchDateShort }}
                                                    </p>
                                                @endif
                                            </div>
                                            <p class="col-3 match-formate {{ $matchFormatClass }}">
                                                @if($matchFormat === 'T20')
                                                    <span class="t20-series">{{ $matchFormat }}</span>
                                                @elseif($matchFormat === 'TEST')
                                                    <span class="test-series">{{ $matchFormat }}</span>
                                                @else
                                                    <span>{{ $matchFormat }}</span>
                                                @endif
                                            </p>
                                            <div class="col-3 mt-1">
                                                @if($state === 'in progress')
                                                    <span class="badge bg-success">Live<span class="animation"></span></span>
                                                @elseif($state === 'complete')
                                                    <span class="badge bg-danger">Result</span>
                                                @elseif($state === 'upcoming')
                                                    <span class="badge bg-secondary">Upcoming</span>
                                                @else
                                                    <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                                @endif
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="margin-top:10px;">
                                                <div class="d-flex align-items-center">
                                                    @if($team1Img)
                                                        <img class="img" src="{{ $team1Img }}" alt="{{ $team1Name }}" />
                                                    @endif
                                                    <span class="{{ $team1CssClass }}" style="margin-left:6px;">{{ $team1Name }}</span>
                                                </div>
                                                <div>
                                                    @if(!empty($t1Score))
                                                        <span class="score-span {{ $team1CssClass }}">
                                                            {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }} ({{ $t1Score['overs'] ?? '-' }} ovs)
                                                        </span>
                                                    @endif
                                                    @if($matchFormat === 'TEST' && !empty($t1Score2))
                                                        <br>
                                                        <span class="score-span {{ $team1CssClass }}">
                                                            {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }} ({{ $t1Score2['overs'] ?? '-' }} ovs)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="margin-top:5px;">
                                                <div class="d-flex align-items-center">
                                                    @if($team2Img)
                                                        <img class="img" src="{{ $team2Img }}" alt="{{ $team2Name }}" />
                                                    @endif
                                                    <span class="{{ $team2CssClass }}" style="margin-left:6px;">{{ $team2Name }}</span>
                                                </div>
                                                <div>
                                                    @if(!empty($t2Score))
                                                        <span class="score-span {{ $team2CssClass }}">
                                                            {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }} ({{ $t2Score['overs'] ?? '-' }} ovs)
                                                        </span>
                                                    @endif
                                                    @if($matchFormat === 'TEST' && !empty($t2Score2))
                                                        <br>
                                                        <span class="score-span {{ $team2CssClass }}">
                                                            {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }} ({{ $t2Score2['overs'] ?? '-' }} ovs)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                @if($state === 'complete')
                                                    <p class="card-text status-complete">{{ $status }}</p>
                                                @else
                                                    <p class="card-text status-else">{{ $status }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <!-- Result Tab Section -->
            <section id="result_section" style="display:{{ $activeTab == 'result' ? 'block' : 'none' }};">
                <div class="container-fluid">
                    <h3 class="text-center">Cricket Result Matches</h3>
                    <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
                        @if(isset($error) && $activeTab == 'result')
                            <span style="color:red;">{{ $error }}</span>
                        @elseif(isset($result) && count($result) === 0 && $activeTab == 'result')
                            <p>No recent matches found.</p>
                        @elseif(isset($result) && $activeTab == 'result')
                            @php
                                usort($result, function($a, $b) {
                                    $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] : (isset($a['startDate']) ? $a['startDate'] : 0);
                                    $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] : (isset($b['startDate']) ? $b['startDate'] : 0);
                                    return $bDate <=> $aDate;
                                });
                            @endphp
                            @foreach($result as $match)
                                @php
                                    $mi = (isset($match['matchInfo']) && is_array($match['matchInfo'])) ? $match['matchInfo'] : [];
                                    $score = (isset($match['matchScore']) && is_array($match['matchScore'])) ? $match['matchScore'] : [];
                                    $team1 = $mi['team1'] ?? [];
                                    $team2 = $mi['team2'] ?? [];
                                    $match_id = $mi['matchId'] ?? ($match['matchId'] ?? '');
                                    $team1Name = $team1['teamName'] ?? 'Team 1';
                                    $team2Name = $team2['teamName'] ?? 'Team 2';
                                    $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
                                    $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
                                    $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg' : '';
                                    $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg' : '';
                                    $matchFormat = strtoupper($mi['matchFormat'] ?? '');
                                    $startDate = isset($mi['startDate']) ? ((int)$mi['startDate'] / 1000) : null;
                                    $matchDateShort = $startDate ? date('d M', $startDate) : '';
                                    $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' : ($matchFormat === 'TEST' ? 'match-format-test' : ''));
                                    $state = strtolower($mi['state'] ?? '');
                                    $status = $mi['status'] ?? '';
                                    $matchDesc = $mi['matchDesc'] ?? 'Match';
                                    $seriesName = $mi['seriesName'] ?? '';
                                    $t1Score = $score['team1Score']['inngs1'] ?? [];
                                    $t1Score2 = $score['team1Score']['inngs2'] ?? [];
                                    $t2Score = $score['team2Score']['inngs1'] ?? [];
                                    $t2Score2 = $score['team2Score']['inngs2'] ?? [];
                                    $winnerId = $score['winningTeamId'] ?? '';
                                    $team1Id = $team1['teamId'] ?? '';
                                    $team2Id = $team2['teamId'] ?? '';
                                    $winnerName = '';
                                    $team1Won = false; $team2Won = false;
                                    if (!empty($status) && preg_match('/^(.*?)\swon\b/i', $status, $m)) {
                                        $winnerName = trim($m[1]);
                                    }
                                    if (strcasecmp($winnerName, $team1Name) == 0) { $team1Won = true; $team2Won = false; }
                                    elseif (strcasecmp($winnerName, $team2Name) == 0) { $team1Won = false; $team2Won = true; }
                                    elseif(!empty($winnerId)) {
                                        $team1Won = ($winnerId && ($winnerId == $team1Id)); $team2Won = ($winnerId && ($winnerId == $team2Id));
                                    }
                                    $team1CssClass = $team1Won ? 'winner-team' : 'loser-team';
                                    $team2CssClass = $team2Won ? 'winner-team' : 'loser-team';
                                @endphp
                                <div class="col match-item">
                                    <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}" style="text-decoration: none; color:#141010;">
                                        <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                                            <div class="row card-body">
                                                <div class="col-7">
                                                    <p class="card-text mb-0" style="color: #817373; font-size:14px">
                                                        <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                                    </p>
                                                    @if(!empty($matchDateShort))
                                                        <p class="card-text mb-0 mt-1" style="color: #5d6570; font-size:12px;">
                                                            Match Date: {{ $matchDateShort }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <p class="col-2 match-formate {{ $matchFormatClass }}">
                                                    @if($matchFormat === 'T20')
                                                        <span class="t20-series">{{ $matchFormat }}</span>
                                                    @elseif($matchFormat === 'TEST')
                                                        <span class="test-series">{{ $matchFormat }}</span>
                                                    @else
                                                        <span>{{ $matchFormat }}</span>
                                                    @endif
                                                </p>
                                                <div class="col-3 mt-1">
                                                    @if($state === 'in progress')
                                                        <span class="badge bg-success">Live<span class="animation"></span></span>
                                                    @elseif($state === 'complete')
                                                        <span class="badge bg-danger">Result</span>
                                                    @elseif($state === 'upcoming')
                                                        <span class="badge bg-secondary">Upcoming</span>
                                                    @else
                                                        <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center" style="margin-top:10px;">
                                                    <div class="d-flex align-items-center">
                                                        @if($team1Img)
                                                            <img class="img" src="{{ $team1Img }}" alt="{{ $team1Name }}" />
                                                        @endif
                                                        <span class="{{ $team1CssClass }}" style="margin-left:6px;">{{ $team1Name }}</span>
                                                    </div>
                                                    <div>
                                                        @if(!empty($t1Score))
                                                            <span class="score-span {{ $team1CssClass }}">
                                                                {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }} ({{ $t1Score['overs'] ?? '-' }} ovs)
                                                            </span>
                                                        @endif
                                                        @if($matchFormat === 'TEST' && !empty($t1Score2))
                                                            <br>
                                                            <span class="score-span {{ $team1CssClass }}">
                                                                {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }} ({{ $t1Score2['overs'] ?? '-' }} ovs)
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center" style="margin-top:5px;">
                                                    <div class="d-flex align-items-center">
                                                        @if($team2Img)
                                                            <img class="img" src="{{ $team2Img }}" alt="{{ $team2Name }}" />
                                                        @endif
                                                        <span class="{{ $team2CssClass }}" style="margin-left:6px;">{{ $team2Name }}</span>
                                                    </div>
                                                    <div>
                                                        @if(!empty($t2Score))
                                                            <span class="score-span {{ $team2CssClass }}">
                                                                {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }} ({{ $t2Score['overs'] ?? '-' }} ovs)
                                                            </span>
                                                        @endif
                                                        @if($matchFormat === 'TEST' && !empty($t2Score2))
                                                            <br>
                                                            <span class="score-span {{ $team2CssClass }}">
                                                                {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }} ({{ $t2Score2['overs'] ?? '-' }} ovs)
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    @if($state === 'complete')
                                                        <p class="card-text status-complete">{{ $status }}</p>
                                                    @else
                                                        <p class="card-text status-else">{{ $status }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </section>

            <!-- Upcoming Tab Section -->
            <section id="upcoming_section" style="display:{{ $activeTab == 'upcoming' ? 'block' : 'none' }};">
                <div class="container-fluid">
                    <h3 class="text-center">Cricket Upcoming Matches</h3>
                    <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
                        @if(isset($error) && $activeTab == 'upcoming')
                            <span style="color:red;">{{ $error }}</span>
                        @elseif(isset($sduling) && count($sduling) === 0 && $activeTab == 'upcoming')
                            <p>No upcoming matches found.</p>
                        @elseif(isset($sduling) && $activeTab == 'upcoming')
                            @php
                                usort($sduling, function($a, $b) {
                                    $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] : (isset($a['startDate']) ? $a['startDate'] : 0);
                                    $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] : (isset($b['startDate']) ? $b['startDate'] : 0);
                                    return $bDate <=> $aDate;
                                });
                            @endphp
                            @foreach($sduling as $match)
                                @php
                                    $mi = (isset($match['matchInfo']) && is_array($match['matchInfo'])) ? $match['matchInfo'] : [];
                                    $score = (isset($match['matchScore']) && is_array($match['matchScore'])) ? $match['matchScore'] : [];
                                    $team1 = $mi['team1'] ?? [];
                                    $team2 = $mi['team2'] ?? [];
                                    $match_id = $mi['matchId'] ?? ($match['matchId'] ?? '');
                                    $team1Name = $team1['teamName'] ?? 'Team 1';
                                    $team2Name = $team2['teamName'] ?? 'Team 2';
                                    $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
                                    $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
                                    $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg' : '';
                                    $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg' : '';
                                    $matchFormat = strtoupper($mi['matchFormat'] ?? '');
                                    $startDate = isset($mi['startDate']) ? ((int)$mi['startDate'] / 1000) : null;
                                    $matchDateShort = $startDate ? date('d M', $startDate) : '';
                                    $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' : ($matchFormat === 'TEST' ? 'match-format-test' : ''));
                                    $state = strtolower($mi['state'] ?? '');
                                    $status = $mi['status'] ?? '';
                                    $matchDesc = $mi['matchDesc'] ?? 'Match';
                                    $seriesName = $mi['seriesName'] ?? '';
                                    $t1Score = $score['team1Score']['inngs1'] ?? [];
                                    $t1Score2 = $score['team1Score']['inngs2'] ?? [];
                                    $t2Score = $score['team2Score']['inngs1'] ?? [];
                                    $t2Score2 = $score['team2Score']['inngs2'] ?? [];
                                    $winnerId = $score['winningTeamId'] ?? '';
                                    $team1Id = $team1['teamId'] ?? '';
                                    $team2Id = $team2['teamId'] ?? '';
                                    $winnerName = '';
                                    $team1Won = false; $team2Won = false;
                                    if (!empty($status) && preg_match('/^(.*?)\swon\b/i', $status, $m)) {
                                        $winnerName = trim($m[1]);
                                    }
                                    if (strcasecmp($winnerName, $team1Name) == 0) { $team1Won = true; $team2Won = false; }
                                    elseif (strcasecmp($winnerName, $team2Name) == 0) { $team1Won = false; $team2Won = true; }
                                    elseif(!empty($winnerId)) {
                                        $team1Won = ($winnerId && ($winnerId == $team1Id)); $team2Won = ($winnerId && ($winnerId == $team2Id));
                                    }
                                    $team1CssClass = $team1Won ? 'winner-team' : 'loser-team';
                                    $team2CssClass = $team2Won ? 'winner-team' : 'loser-team';
                                @endphp
                                <div class="col match-item">
                                    <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}" style="text-decoration: none; color:#141010;">
                                        <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                                            <div class="row card-body">
                                                <div class="col-7">
                                                    <p class="card-text mb-0" style="color: #817373; font-size:14px">
                                                        <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                                    </p>
                                                    @if(!empty($matchDateShort))
                                                        <p class="card-text mb-0 mt-1" style="color: #5d6570; font-size:12px;">
                                                            Match Date: {{ $matchDateShort }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <p class="col-2 match-formate {{ $matchFormatClass }}">
                                                    @if($matchFormat === 'T20')
                                                        <span class="t20-series">{{ $matchFormat }}</span>
                                                    @elseif($matchFormat === 'TEST')
                                                        <span class="test-series">{{ $matchFormat }}</span>
                                                    @else
                                                        <span>{{ $matchFormat }}</span>
                                                    @endif
                                                </p>
                                                <div class="col-3 mt-1">
                                                    @if($state === 'in progress')
                                                        <span class="badge bg-success">Live<span class="animation"></span></span>
                                                    @elseif($state === 'complete')
                                                        <span class="badge bg-danger">Result</span>
                                                    @elseif($state === 'upcoming' || $state === 'preview')
                                                        <span class="badge bg-secondary">Upcoming</span>
                                                    @else
                                                        <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center" style="margin-top:10px;">
                                                    <div class="d-flex align-items-center">
                                                        @if($team1Img)
                                                            <img class="img" src="{{ $team1Img }}" alt="{{ $team1Name }}" />
                                                        @endif
                                                        <span class="{{ $team1CssClass }}" style="margin-left:6px;">{{ $team1Name }}</span>
                                                    </div>
                                                    <div>
                                                        @if(!empty($t1Score))
                                                            <span class="score-span {{ $team1CssClass }}">
                                                                {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }} ({{ $t1Score['overs'] ?? '-' }} ovs)
                                                            </span>
                                                        @endif
                                                        @if($matchFormat === 'TEST' && !empty($t1Score2))
                                                            <br>
                                                            <span class="score-span {{ $team1CssClass }}">
                                                                {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }} ({{ $t1Score2['overs'] ?? '-' }} ovs)
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center" style="margin-top:5px;">
                                                    <div class="d-flex align-items-center">
                                                        @if($team2Img)
                                                            <img class="img" src="{{ $team2Img }}" alt="{{ $team2Name }}" />
                                                        @endif
                                                        <span class="{{ $team2CssClass }}" style="margin-left:6px;">{{ $team2Name }}</span>
                                                    </div>
                                                    <div>
                                                        @if(!empty($t2Score))
                                                            <span class="score-span {{ $team2CssClass }}">
                                                                {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }} ({{ $t2Score['overs'] ?? '-' }} ovs)
                                                            </span>
                                                        @endif
                                                        @if($matchFormat === 'TEST' && !empty($t2Score2))
                                                            <br>
                                                            <span class="score-span {{ $team2CssClass }}">
                                                                {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }} ({{ $t2Score2['overs'] ?? '-' }} ovs)
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    @if($state === 'complete')
                                                        <p class="card-text status-complete">{{ $status }}</p>
                                                    @else
                                                        <p class="card-text status-else">{{ $status }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </section>
        </div>
@endsection
