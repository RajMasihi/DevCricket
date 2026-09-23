@extends('layouts.main')

@section('title', $pageTitle ?? 'CricketLiveScore')

@section('meta-description')
    <meta name="description" content="{{ $metaDescription ?? 'Criclivem - Live cricket scores, match updates, schedules, news, and ICC rankings. Stay updated with latest cricket action from around the world including international matches, domestic leagues, and women cricket.' }}">
@endsection

@section('meta-keywords')
    <meta name="keywords" content="{{ $metaKeywords ?? 'cricket, live cricket scores, cricket news, ICC rankings, cricket schedule, T20, ODI, Test cricket, IPL, BBL, CPL, international cricket, domestic cricket, women cricket' }}">
@endsection

@section('main-container')
<style>
.active-tab {
    border-bottom: 2px solid #053259 !important;
}

/* Slider Styles */
.match-slider-container {
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}

.match-slider-wrapper {
    display: flex;
    transition: transform 0.5s ease-in-out;
    gap: 15px;
}

.match-slider-item {
    flex: 0 0 calc(33.333% - 10px);
    min-width: 300px;
}

.slider-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #053259;
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.slider-nav-btn:hover {
    background: #045aa5;
}

.slider-nav-btn.prev {
    left: 10px;
}

.slider-nav-btn.next {
    right: 10px;
}

.slider-nav-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .match-slider-item {
        flex: 0 0 calc(50% - 10px);
        min-width: 250px;
    }
}

@media (max-width: 576px) {
    .match-slider-item {
        flex: 0 0 100%;
        min-width: 100%;
    }
}
</style>
@php
$activeTab = request()->get('tab', 'live');
if (!in_array($activeTab, ['live', 'result', 'upcoming', 'recent'])) {
$activeTab = 'live';
}
@endphp
<div class="container-fluid main-section" id="cricket-index-page" data-active-tab="{{ e($activeTab) }}">
    <div id="live_section" style="display:{{ $activeTab == 'live' ? 'block' : 'none' }};">
        <h4 class="text-center mb-3">Live Score</h4>
    </div>
    <!-- <div id="result_section" style="display:{{ $activeTab == 'result' ? 'block' : 'none' }};">
        <h4 class="text-center mb-3">Result Matches</h4>
    </div> -->
    <div id="upcoming_section" style="display:{{ $activeTab == 'upcoming' ? 'block' : 'none' }};">
        <h4 class="text-center mb-3">Upcoming Matches</h4>
    </div>
    <div id="recent_section" style="display:{{ $activeTab == 'recent' ? 'block' : 'none' }};">
        <h4 class="text-center mb-3">Recent Matches</h4>
    </div>
    <div class="d-flex pb-3">
        <a href="{{ url('/?tab=live') }}" id="live_tab_btn"
            class="btn me-2 scoreboard-title{{ $activeTab == 'live' ? ' active-tab' : '' }}" type="button">

            Live Score

        </a>
        <!-- <a href="{{ url('/result?tab=result') }}" id="result_tab_btn"
            class="btn me-2 scoreboard-title{{ $activeTab == 'result' ? ' active-tab' : '' }}" type="button">
            Result
        </a> -->
        <a href="{{ route('upcoming-matches', ['tab' => 'upcoming']) }}" id="upcoming_tab_btn"
            class="btn me-2 scoreboard-title{{ $activeTab == 'upcoming' ? ' active-tab' : '' }}" type="button">
            Upcoming
        </a>
        <a href="{{ url('/?tab=recent') }}" id="recent_tab_btn"
            class="btn me-2 scoreboard-title{{ $activeTab == 'recent' ? ' active-tab' : '' }}" type="button">
            Result
        </a>
    </div>

    <!-- Featured Matches Slider -->
    @if(isset($matches) && count($matches) > 0)
    <div class="match-slider-container mb-4">
        <button class="slider-nav-btn prev" id="sliderPrevBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="match-slider-wrapper" id="matchSlider">
            @php
                $sliderMatches = array_slice($matches, 0, 6); // Take first 6 matches for slider
            @endphp
            @foreach($sliderMatches as $match)
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
                    $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ?
                    'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg' : '';
                    $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ?
                    'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg' : '';
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
                <div class="match-slider-item">
                    <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}"
                        style="text-decoration: none; color:#141010;">
                        <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                            <div class="card shadow-sm w-100">
                                <div class="card-body p-3">
                                    <!-- Top Row: Match Info, Format, & State -->
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="overflow-hidden">
                                            <p class="card-text text-truncate mb-0"
                                                style="color: #817373; font-size: 14px;">
                                                <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                            </p>
                                            @if(!empty($matchDateShort))
                                            <p class="card-text mb-0 mt-1" style="color: #5d6570; font-size: 12px;">
                                                Match Date: {{ $matchDateShort }}
                                            </p>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            <div class="match-formate mb-0 {{ $matchFormatClass }}">
                                                @if($matchFormat === 'T20')
                                                <span class="badge text-light border t20-series">{{ $matchFormat }}</span>
                                                @elseif($matchFormat === 'TEST')
                                                <span class="badge bg-light text-dark border test-series">{{ $matchFormat }}</span>
                                                @else
                                                <span class="badge text-dark border odi-series">{{ $matchFormat }}</span>
                                                @endif
                                            </div>

                                            <div>
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
                                        </div>
                                    </div>

                                    <hr class="my-2 opacity-25">

                                    <!-- Team 1 Row -->
                                    <div class="d-flex justify-content-between align-items-center my-2">
                                        <div class="d-flex align-items-center overflow-hidden me-2">
                                            @if($team1Img)
                                            <img class="img flex-shrink-0 me-2" src="{{ $team1Img }}"
                                                alt="{{ $team1Name }}"
                                                style="width:24px; height:24px; object-fit:contain;" />
                                            @endif
                                            <span class="{{ $team1CssClass }} text-truncate"
                                                style="font-size: 14px; font-weight: 600;">{{ $team1Name }}</span>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            @if(!empty($t1Score))
                                            <span class="score-span {{ $team1CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }}
                                                ({{ $t1Score['overs_display'] ?? $t1Score['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                            @if($matchFormat === 'TEST' && !empty($t1Score2))
                                            <br>
                                            <span class="score-span {{ $team1CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }}
                                                ({{ $t1Score2['overs_display'] ?? $t1Score2['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Team 2 Row -->
                                    <div class="d-flex justify-content-between align-items-center my-2">
                                        <div class="d-flex align-items-center overflow-hidden me-2">
                                            @if($team2Img)
                                            <img class="img flex-shrink-0 me-2" src="{{ $team2Img }}"
                                                alt="{{ $team2Name }}"
                                                style="width:24px; height:24px; object-fit:contain;" />
                                            @endif
                                            <span class="{{ $team2CssClass }} text-truncate"
                                                style="font-size: 14px; font-weight: 600;">{{ $team2Name }}</span>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            @if(!empty($t2Score))
                                            <span class="score-span {{ $team2CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }}
                                                ({{ $t2Score['overs_display'] ?? $t2Score['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                            @if($matchFormat === 'TEST' && !empty($t2Score2))
                                            <br>
                                            <span class="score-span {{ $team2CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }}
                                                ({{ $t2Score2['overs_display'] ?? $t2Score2['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Match Status Message -->
                                    <div class="mt-2 pt-1 border-top">
                                        @if($state === 'complete')
                                        <p class="card-text status-complete text-danger mb-0"
                                            style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                        @else
                                        <p class="card-text status-else text-muted mb-0"
                                            style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <button class="slider-nav-btn next" id="sliderNextBtn">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    @endif

    <div id="tab-sections">
        <!-- Live Tab Section -->
        <section id="live_section" style="display:{{ $activeTab == 'live' ? 'block' : 'none' }};">
            <div class="row row-cols-1 row-cols-md-2 g-4 pt-2" id="live_matches_container">
                @if(isset($error) && $activeTab == 'live')

                <span style="color:red;">Wait ...</span>

                @elseif(isset($matches) && count($matches) === 0 && $activeTab == 'live')
                <p>No recent matches found.</p>
                @elseif(isset($matches) && $activeTab == 'live')
                @if(isset($hasLiveMatches) && $hasLiveMatches)
                <!-- Live Matches Section -->
                <div class="col-12 mb-3">
                    <h5 class="text-primary fw-bold border-bottom pb-2">Live Matches</h5>
                </div>
                @php
                usort($liveMatches, function($a, $b) {
                $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] : (isset($a['startDate']) ?
                $a['startDate'] : 0);
                $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] : (isset($b['startDate']) ?
                $b['startDate'] : 0);
                return $bDate <=> $aDate;
                    });
                    @endphp
                    @foreach($liveMatches as $match)
                    @php
                    $mi = (isset($match['matchInfo']) && is_array($match['matchInfo'])) ? $match['matchInfo'] : [];
                    $score = (isset($match['matchScore']) && is_array($match['matchScore'])) ? $match['matchScore'] :
                    [];
                    $team1 = $mi['team1'] ?? [];
                    $team2 = $mi['team2'] ?? [];
                    $match_id = $mi['matchId'] ?? ($match['matchId'] ?? '');
                    $team1Name = $team1['teamName'] ?? 'Team 1';
                    $team2Name = $team2['teamName'] ?? 'Team 2';
                    $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
                    $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
                    $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ?
                    'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg'
                    : '';
                    $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ?
                    'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg'
                    : '';
                    $startDate = isset($mi['startDate']) ? ((int)$mi['startDate'] / 1000) : null;
                    $localTime = $startDate ? date('d M, Y h:i A', $startDate) : '';
                    $matchDateShort = $startDate ? date('d M', $startDate) : '';
                    $matchFormat = strtoupper($mi['matchFormat'] ?? '');
                    $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' :
                    ($matchFormat === 'TEST' ? 'match-format-test' : ''));
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
                    $team1Won = ($winnerId && ($winnerId == $team1Id)); $team2Won = ($winnerId && ($winnerId ==
                    $team2Id));
                    }
                    $team1CssClass = $team1Won ? 'winner-team' : 'loser-team';
                    $team2CssClass = $team2Won ? 'winner-team' : 'loser-team';
                    @endphp
                    <div class="col match-item" data-match-id="{{ $match_id }}">
                        <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}"
                            style="text-decoration: none; color:#141010;">
                            <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                                <div class="card shadow-sm w-100">
                                    <div class="card-body p-3">
                                        <!-- Top Row: Match Info, Format, & State -->
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <div class="overflow-hidden">
                                                <p class="card-text text-truncate mb-0"
                                                    style="color: #817373; font-size: 14px;">
                                                    <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                                </p>
                                                @if(!empty($matchDateShort))
                                                <p class="card-text mb-0 mt-1" style="color: #5d6570; font-size: 12px;">
                                                    Match Date: {{ $matchDateShort }}
                                                </p>
                                                @endif
                                            </div>

                                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                <div class="match-formate mb-0 {{ $matchFormatClass }}">
                                                    @if($matchFormat === 'T20')
                                                    <span
                                                        class="badge  text-light border t20-series">{{ $matchFormat }}</span>
                                                    @elseif($matchFormat === 'TEST')
                                                    <span
                                                        class="badge bg-light text-dark border test-series">{{ $matchFormat }}</span>
                                                    @else
                                                    <span
                                                        class="badge text-dark border odi-series">{{ $matchFormat }}</span>
                                                    @endif
                                                </div>

                                                <div>
                                                    @if($state === 'in progress')
                                                    <span class="badge bg-success">Live<span
                                                            class="animation"></span></span>
                                                    @elseif($state === 'complete')
                                                    <span class="badge bg-danger">Result</span>
                                                    @elseif($state === 'upcoming')
                                                    <span class="badge bg-secondary">Upcoming</span>
                                                    @else
                                                    <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-2 opacity-25">

                                        <!-- Team 1 Row -->
                                        <div class="d-flex justify-content-between align-items-center my-2">
                                            <div class="d-flex align-items-center overflow-hidden me-2">
                                                @if($team1Img)
                                                <img class="img flex-shrink-0 me-2" src="{{ $team1Img }}"
                                                    alt="{{ $team1Name }}"
                                                    style="width:24px; height:24px; object-fit:contain;" />
                                                @endif
                                                <span class="{{ $team1CssClass }} text-truncate"
                                                    style="font-size: 14px; font-weight: 600;">{{ $team1Name }}</span>
                                            </div>
                                            <div class="text-end flex-shrink-0">
                                                @if(!empty($t1Score))
                                                <span class="score-span {{ $team1CssClass }}"
                                                    style="font-size: 13px; font-weight: 600;">
                                                    {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }}
                                                    ({{ $t1Score['overs_display'] ?? $t1Score['overs'] ?? '-' }} ovs)
                                                </span>
                                                @endif
                                                @if($matchFormat === 'TEST' && !empty($t1Score2))
                                                <br>
                                                <span class="score-span {{ $team1CssClass }}"
                                                    style="font-size: 13px; font-weight: 600;">
                                                    {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }}
                                                    ({{ $t1Score2['overs_display'] ?? $t1Score2['overs'] ?? '-' }} ovs)
                                                </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Team 2 Row -->
                                        <div class="d-flex justify-content-between align-items-center my-2">
                                            <div class="d-flex align-items-center overflow-hidden me-2">
                                                @if($team2Img)
                                                <img class="img flex-shrink-0 me-2" src="{{ $team2Img }}"
                                                    alt="{{ $team2Name }}"
                                                    style="width:24px; height:24px; object-fit:contain;" />
                                                @endif
                                                <span class="{{ $team2CssClass }} text-truncate"
                                                    style="font-size: 14px; font-weight: 600;">{{ $team2Name }}</span>
                                            </div>
                                            <div class="text-end flex-shrink-0">
                                                @if(!empty($t2Score))
                                                <span class="score-span {{ $team2CssClass }}"
                                                    style="font-size: 13px; font-weight: 600;">
                                                    {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }}
                                                    ({{ $t2Score['overs_display'] ?? $t2Score['overs'] ?? '-' }} ovs)
                                                </span>
                                                @endif
                                                @if($matchFormat === 'TEST' && !empty($t2Score2))
                                                <br>
                                                <span class="score-span {{ $team2CssClass }}"
                                                    style="font-size: 13px; font-weight: 600;">
                                                    {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }}
                                                    ({{ $t2Score2['overs_display'] ?? $t2Score2['overs'] ?? '-' }} ovs)
                                                </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Match Status Message -->
                                        <div class="mt-2 pt-1 border-top">
                                            @if($state === 'complete')
                                            <p class="card-text status-complete text-danger mb-0"
                                                style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                            @else
                                            <p class="card-text status-else text-muted mb-0"
                                                style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach

                    <!-- Recent Matches Section -->
                    @if(isset($recentMatches) && count($recentMatches) > 0)
                    <div class="col-12 mb-3 mt-4">
                        <h5 class="text-primary fw-bold border-bottom pb-2">Recently Updated Matches</h5>
                    </div>
                    @php
                    usort($recentMatches, function($a, $b) {
                    $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] :
                    (isset($a['startDate']) ? $a['startDate'] : 0);
                    $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] :
                    (isset($b['startDate']) ? $b['startDate'] : 0);
                    return $bDate <=> $aDate;
                        });
                        @endphp
                        @foreach(array_slice($recentMatches, 0, 3) as $match)
                        @php
                        $mi = (isset($match['matchInfo']) && is_array($match['matchInfo'])) ? $match['matchInfo'] : [];
                        $score = (isset($match['matchScore']) && is_array($match['matchScore'])) ? $match['matchScore']
                        : [];
                        $team1 = $mi['team1'] ?? [];
                        $team2 = $mi['team2'] ?? [];
                        $match_id = $mi['matchId'] ?? ($match['matchId'] ?? '');
                        $team1Name = $team1['teamName'] ?? 'Team 1';
                        $team2Name = $team2['teamName'] ?? 'Team 2';
                        $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
                        $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
                        $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ?
                        'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug .
                        '.jpg' : '';
                        $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ?
                        'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug .
                        '.jpg' : '';
                        $startDate = isset($mi['startDate']) ? ((int)$mi['startDate'] / 1000) : null;
                        $localTime = $startDate ? date('d M, Y h:i A', $startDate) : '';
                        $matchDateShort = $startDate ? date('d M', $startDate) : '';
                        $matchFormat = strtoupper($mi['matchFormat'] ?? '');
                        $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' :
                        ($matchFormat === 'TEST' ? 'match-format-test' : ''));
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
                        $team1Won = ($winnerId && ($winnerId == $team1Id)); $team2Won = ($winnerId && ($winnerId ==
                        $team2Id));
                        }
                        $team1CssClass = $team1Won ? 'winner-team' : 'loser-team';
                        $team2CssClass = $team2Won ? 'winner-team' : 'loser-team';
                        @endphp
                        <div class="col match-item" data-match-id="{{ $match_id }}">
                            <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}"
                                style="text-decoration: none; color:#141010;">
                                <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                                    <div class="card shadow-sm w-100">
                                        <div class="card-body p-3">
                                            <!-- Top Row: Match Info, Format, & State -->
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <div class="overflow-hidden">
                                                    <p class="card-text text-truncate mb-0"
                                                        style="color: #817373; font-size: 14px;">
                                                        <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                                    </p>
                                                    @if(!empty($matchDateShort))
                                                    <p class="card-text mb-0 mt-1"
                                                        style="color: #5d6570; font-size: 12px;">
                                                        Match Date: {{ $matchDateShort }}
                                                    </p>
                                                    @endif
                                                </div>

                                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                    <div class="match-formate mb-0 {{ $matchFormatClass }}">
                                                        @if($matchFormat === 'T20')
                                                        <span
                                                            class="badge  text-light border t20-series">{{ $matchFormat }}</span>
                                                        @elseif($matchFormat === 'TEST')
                                                        <span
                                                            class="badge bg-light text-dark border test-series">{{ $matchFormat }}</span>
                                                        @else
                                                        <span
                                                            class="badge text-dark border odi-series">{{ $matchFormat }}</span>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        @if($state === 'in progress')
                                                        <span class="badge bg-success">Live<span
                                                                class="animation"></span></span>
                                                        @elseif($state === 'complete')
                                                        <span class="badge bg-danger">Result</span>
                                                        @elseif($state === 'upcoming')
                                                        <span class="badge bg-secondary">Upcoming</span>
                                                        @else
                                                        <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-2 opacity-25">

                                            <!-- Team 1 Row -->
                                            <div class="d-flex justify-content-between align-items-center my-2">
                                                <div class="d-flex align-items-center overflow-hidden me-2">
                                                    @if($team1Img)
                                                    <img class="img flex-shrink-0 me-2" src="{{ $team1Img }}"
                                                        alt="{{ $team1Name }}"
                                                        style="width:24px; height:24px; object-fit:contain;" />
                                                    @endif
                                                    <span class="{{ $team1CssClass }} text-truncate"
                                                        style="font-size: 14px; font-weight: 600;">{{ $team1Name }}</span>
                                                </div>
                                                <div class="text-end flex-shrink-0">
                                                    @if(!empty($t1Score))
                                                    <span class="score-span {{ $team1CssClass }}"
                                                        style="font-size: 13px; font-weight: 600;">
                                                        {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }}
                                                        ({{ $t1Score['overs_display'] ?? $t1Score['overs'] ?? '-' }}
                                                        ovs)
                                                    </span>
                                                    @endif
                                                    @if($matchFormat === 'TEST' && !empty($t1Score2))
                                                    <br>
                                                    <span class="score-span {{ $team1CssClass }}"
                                                        style="font-size: 13px; font-weight: 600;">
                                                        {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }}
                                                        ({{ $t1Score2['overs_display'] ?? $t1Score2['overs'] ?? '-' }}
                                                        ovs)
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Team 2 Row -->
                                            <div class="d-flex justify-content-between align-items-center my-2">
                                                <div class="d-flex align-items-center overflow-hidden me-2">
                                                    @if($team2Img)
                                                    <img class="img flex-shrink-0 me-2" src="{{ $team2Img }}"
                                                        alt="{{ $team2Name }}"
                                                        style="width:24px; height:24px; object-fit:contain;" />
                                                    @endif
                                                    <span class="{{ $team2CssClass }} text-truncate"
                                                        style="font-size: 14px; font-weight: 600;">{{ $team2Name }}</span>
                                                </div>
                                                <div class="text-end flex-shrink-0">
                                                    @if(!empty($t2Score))
                                                    <span class="score-span {{ $team2CssClass }}"
                                                        style="font-size: 13px; font-weight: 600;">
                                                        {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }}
                                                        ({{ $t2Score['overs_display'] ?? $t2Score['overs'] ?? '-' }}
                                                        ovs)
                                                    </span>
                                                    @endif
                                                    @if($matchFormat === 'TEST' && !empty($t2Score2))
                                                    <br>
                                                    <span class="score-span {{ $team2CssClass }}"
                                                        style="font-size: 13px; font-weight: 600;">
                                                        {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }}
                                                        ({{ $t2Score2['overs_display'] ?? $t2Score2['overs'] ?? '-' }}
                                                        ovs)
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Match Status Message -->
                                            <div class="mt-2 pt-1 border-top">
                                                @if($state === 'complete')
                                                <p class="card-text status-complete text-danger mb-0"
                                                    style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                                @else
                                                <p class="card-text status-else text-muted mb-0"
                                                    style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                        @endif
                        @else
                        <!-- When no live matches, show recent matches without section header -->
                        @php
                        usort($matches, function($a, $b) {
                        $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] :
                        (isset($a['startDate']) ? $a['startDate'] : 0);
                        $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] :
                        (isset($b['startDate']) ? $b['startDate'] : 0);
                        return $bDate <=> $aDate;
                            });
                            @endphp
                            @foreach($matches as $match)
                            @php
                            $mi = (isset($match['matchInfo']) && is_array($match['matchInfo'])) ? $match['matchInfo'] :
                            [];
                            $score = (isset($match['matchScore']) && is_array($match['matchScore'])) ?
                            $match['matchScore'] : [];
                            $team1 = $mi['team1'] ?? [];
                            $team2 = $mi['team2'] ?? [];
                            $match_id = $mi['matchId'] ?? ($match['matchId'] ?? '');
                            $team1Name = $team1['teamName'] ?? 'Team 1';
                            $team2Name = $team2['teamName'] ?? 'Team 2';
                            $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
                            $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
                            $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ?
                            'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug .
                            '.jpg' : '';
                            $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ?
                            'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug .
                            '.jpg' : '';
                            $startDate = isset($mi['startDate']) ? ((int)$mi['startDate'] / 1000) : null;
                            $localTime = $startDate ? date('d M, Y h:i A', $startDate) : '';
                            $matchDateShort = $startDate ? date('d M', $startDate) : '';
                            $matchFormat = strtoupper($mi['matchFormat'] ?? '');
                            $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ?
                            'match-format-t20' : ($matchFormat === 'TEST' ? 'match-format-test' : ''));
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
                            $team1Won = ($winnerId && ($winnerId == $team1Id)); $team2Won = ($winnerId && ($winnerId ==
                            $team2Id));
                            }
                            $team1CssClass = $team1Won ? 'winner-team' : 'loser-team';
                            $team2CssClass = $team2Won ? 'winner-team' : 'loser-team';
                            @endphp
                            <div class="col match-item" data-match-id="{{ $match_id }}">
                                <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}"
                                    style="text-decoration: none; color:#141010;">
                                    <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                                        <div class="card shadow-sm w-100">
                                            <div class="card-body p-3">
                                                <!-- Top Row: Match Info, Format, & State -->
                                                <div
                                                    class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <div class="overflow-hidden">
                                                        <p class="card-text text-truncate mb-0"
                                                            style="color: #817373; font-size: 14px;">
                                                            <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                                        </p>
                                                        @if(!empty($matchDateShort))
                                                        <p class="card-text mb-0 mt-1"
                                                            style="color: #5d6570; font-size: 12px;">
                                                            Match Date: {{ $matchDateShort }}
                                                        </p>
                                                        @endif
                                                    </div>

                                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                        <div class="match-formate mb-0 {{ $matchFormatClass }}">
                                                            @if($matchFormat === 'T20')
                                                            <span
                                                                class="badge  text-light border t20-series">{{ $matchFormat }}</span>
                                                            @elseif($matchFormat === 'TEST')
                                                            <span
                                                                class="badge bg-light text-dark border test-series">{{ $matchFormat }}</span>
                                                            @else
                                                            <span
                                                                class="badge text-dark border odi-series">{{ $matchFormat }}</span>
                                                            @endif
                                                        </div>

                                                        <div>
                                                            @if($state === 'in progress')
                                                            <span class="badge bg-success">Live<span
                                                                    class="animation"></span></span>
                                                            @elseif($state === 'complete')
                                                            <span class="badge bg-danger">Result</span>
                                                            @elseif($state === 'upcoming')
                                                            <span class="badge bg-secondary">Upcoming</span>
                                                            @else
                                                            <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr class="my-2 opacity-25">

                                                <!-- Team 1 Row -->
                                                <div class="d-flex justify-content-between align-items-center my-2">
                                                    <div class="d-flex align-items-center overflow-hidden me-2">
                                                        @if($team1Img)
                                                        <img class="img flex-shrink-0 me-2" src="{{ $team1Img }}"
                                                            alt="{{ $team1Name }}"
                                                            style="width:24px; height:24px; object-fit:contain;" />
                                                        @endif
                                                        <span class="{{ $team1CssClass }} text-truncate"
                                                            style="font-size: 14px; font-weight: 600;">{{ $team1Name }}</span>
                                                    </div>
                                                    <div class="text-end flex-shrink-0">
                                                        @if(!empty($t1Score))
                                                        <span class="score-span {{ $team1CssClass }}"
                                                            style="font-size: 13px; font-weight: 600;">
                                                            {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }}
                                                            ({{ $t1Score['overs_display'] ?? $t1Score['overs'] ?? '-' }}
                                                            ovs)
                                                        </span>
                                                        @endif
                                                        @if($matchFormat === 'TEST' && !empty($t1Score2))
                                                        <br>
                                                        <span class="score-span {{ $team1CssClass }}"
                                                            style="font-size: 13px; font-weight: 600;">
                                                            {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }}
                                                            ({{ $t1Score2['overs_display'] ?? $t1Score2['overs'] ?? '-' }}
                                                            ovs)
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Team 2 Row -->
                                                <div class="d-flex justify-content-between align-items-center my-2">
                                                    <div class="d-flex align-items-center overflow-hidden me-2">
                                                        @if($team2Img)
                                                        <img class="img flex-shrink-0 me-2" src="{{ $team2Img }}"
                                                            alt="{{ $team2Name }}"
                                                            style="width:24px; height:24px; object-fit:contain;" />
                                                        @endif
                                                        <span class="{{ $team2CssClass }} text-truncate"
                                                            style="font-size: 14px; font-weight: 600;">{{ $team2Name }}</span>
                                                    </div>
                                                    <div class="text-end flex-shrink-0">
                                                        @if(!empty($t2Score))
                                                        <span class="score-span {{ $team2CssClass }}"
                                                            style="font-size: 13px; font-weight: 600;">
                                                            {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }}
                                                            ({{ $t2Score['overs_display'] ?? $t2Score['overs'] ?? '-' }}
                                                            ovs)
                                                        </span>
                                                        @endif
                                                        @if($matchFormat === 'TEST' && !empty($t2Score2))
                                                        <br>
                                                        <span class="score-span {{ $team2CssClass }}"
                                                            style="font-size: 13px; font-weight: 600;">
                                                            {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }}
                                                            ({{ $t2Score2['overs_display'] ?? $t2Score2['overs'] ?? '-' }}
                                                            ovs)
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Match Status Message -->
                                                <div class="mt-2 pt-1 border-top">
                                                    @if($state === 'complete')
                                                    <p class="card-text status-complete text-danger mb-0"
                                                        style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                                    @else
                                                    <p class="card-text status-else text-muted mb-0"
                                                        style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            @endforeach
                            @endif
                            @endif
                            
                           
            </div>
        </section>
    </div>

<!-- Result Tab Section -->
<section id="result_section" style="display:{{ $activeTab == 'result' ? 'block' : 'none' }};">
    <div class="container-fluid">

        <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
            @if(isset($error) && $activeTab == 'result')

            <span style="color:red;">Wait ...</span>

            @elseif(isset($result) && count($result) === 0 && $activeTab == 'result')
            <p>No recent matches found.</p>
            @elseif(isset($result) && $activeTab == 'result')
            @php
            usort($result, function($a, $b) {
            $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] : (isset($a['startDate']) ?
            $a['startDate'] : 0);
            $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] : (isset($b['startDate']) ?
            $b['startDate'] : 0);
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
                $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ?
                'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg' :
                '';
                $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ?
                'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg' :
                '';
                $matchFormat = strtoupper($mi['matchFormat'] ?? '');
                $startDate = isset($mi['startDate']) ? ((int)$mi['startDate'] / 1000) : null;
                $matchDateShort = $startDate ? date('d M', $startDate) : '';
                $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' :
                ($matchFormat === 'TEST' ? 'match-format-test' : ''));
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
                <div class="col match-item" data-match-id="{{ $match_id }}">
                    <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}"
                        style="text-decoration: none; color:#141010;">
                        <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                            <div class="card shadow-sm w-100">
                                <div class="card-body p-3">
                                    <!-- Header Row: Match Info, Format & State -->
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="overflow-hidden flex-grow-1">
                                            <p class="card-text text-truncate mb-0"
                                                style="color: #817373; font-size: 14px;">
                                                <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                            </p>
                                            @if(!empty($matchDateShort))
                                            <p class="card-text mb-0 mt-1" style="color: #5d6570; font-size: 12px;">
                                                Match Date: {{ $matchDateShort }}
                                            </p>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            <p class="match-formate mb-0 {{ $matchFormatClass }}">
                                                @if($matchFormat === 'T20')
                                                <span
                                                    class="t20-series badge text-light border">{{ $matchFormat }}</span>
                                                @elseif($matchFormat === 'TEST')
                                                <span
                                                    class="test-series badge bg-light text-dark border">{{ $matchFormat }}</span>
                                                @else
                                                <span class="badge bg-light text-dark border">{{ $matchFormat }}</span>
                                                @endif
                                            </p>

                                            <div>
                                                @if($state === 'in progress')
                                                <span class="badge bg-success">Live<span
                                                        class="animation"></span></span>
                                                @elseif($state === 'complete')
                                                <span class="badge bg-danger">Result</span>
                                                @elseif($state === 'upcoming')
                                                <span class="badge bg-secondary">Upcoming</span>
                                                @else
                                                <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-2 opacity-25">

                                    <!-- Team 1 Row -->
                                    <div class="d-flex justify-content-between align-items-center my-2">
                                        <div class="d-flex align-items-center overflow-hidden me-2">
                                            @if($team1Img)
                                            <img class="img flex-shrink-0 me-2" src="{{ $team1Img }}"
                                                alt="{{ $team1Name }}"
                                                style="width:24px; height:24px; object-fit:contain;" />
                                            @endif
                                            <span class="{{ $team1CssClass }} text-truncate"
                                                style="font-size: 14px; font-weight: 600;">{{ $team1Name }}</span>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            @if(!empty($t1Score))
                                            <span class="score-span {{ $team1CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }}
                                                ({{ $t1Score['overs_display'] ?? $t1Score['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                            @if($matchFormat === 'TEST' && !empty($t1Score2))
                                            <br>
                                            <span class="score-span {{ $team1CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }}
                                                ({{ $t1Score2['overs_display'] ?? $t1Score2['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Team 2 Row -->
                                    <div class="d-flex justify-content-between align-items-center my-2">
                                        <div class="d-flex align-items-center overflow-hidden me-2">
                                            @if($team2Img)
                                            <img class="img flex-shrink-0 me-2" src="{{ $team2Img }}"
                                                alt="{{ $team2Name }}"
                                                style="width:24px; height:24px; object-fit:contain;" />
                                            @endif
                                            <span class="{{ $team2CssClass }} text-truncate"
                                                style="font-size: 14px; font-weight: 600;">{{ $team2Name }}</span>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            @if(!empty($t2Score))
                                            <span class="score-span {{ $team2CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }}
                                                ({{ $t2Score['overs_display'] ?? $t2Score['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                            @if($matchFormat === 'TEST' && !empty($t2Score2))
                                            <br>
                                            <span class="score-span {{ $team2CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }}
                                                ({{ $t2Score2['overs_display'] ?? $t2Score2['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Match Status Footer -->
                                    <div class="mt-2 pt-1 border-top">
                                        @if($state === 'complete')
                                        <p class="card-text status-complete text-danger mb-0"
                                            style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                        @else
                                        <p class="card-text status-else text-muted mb-0"
                                            style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                        @endif
                                    </div>
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
        <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
            @if(isset($error) && $activeTab == 'upcoming')

            <span style="color:red;">Wait ...</span>

            @elseif(isset($upcomingMatches) && is_array($upcomingMatches) && count($upcomingMatches) === 0 && $activeTab == 'upcoming')
            <p>No upcoming matches found.</p>
            @elseif(isset($upcomingMatches) && is_array($upcomingMatches) && $activeTab == 'upcoming')
            @php
            $upcomingDisplay = $upcomingMatches;
            usort($upcomingDisplay, function($a, $b) {
            $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] : (isset($a['startDate']) ?
            $a['startDate'] : 0);
            $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] : (isset($b['startDate']) ?
            $b['startDate'] : 0);
            return $aDate <=> $bDate;
                });
                @endphp
                @foreach($upcomingDisplay as $match)
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
                $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ?
                'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg' :
                '';
                $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ?
                'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg' :
                '';
                $matchFormat = strtoupper($mi['matchFormat'] ?? '');
                $startDate = isset($mi['startDate']) ? ((int)$mi['startDate'] / 1000) : null;
                $matchDateShort = $startDate ? date('d M', $startDate) : '';
                $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' :
                ($matchFormat === 'TEST' ? 'match-format-test' : ''));
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
                <div class="col match-item" data-match-id="{{ $match_id }}">
                    <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}"
                        style="text-decoration: none; color:#141010;">
                        <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                            <div class="card shadow-sm w-100">
                                <div class="card-body p-3">
                                    <!-- Header Row: Match Info, Format & State -->
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="overflow-hidden flex-grow-1">
                                            <p class="card-text text-truncate mb-0"
                                                style="color: #817373; font-size: 14px;">
                                                <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                            </p>
                                            @if(!empty($matchDateShort))
                                            <p class="card-text mb-0 mt-1" style="color: #5d6570; font-size: 12px;">
                                                Match Date: {{ $matchDateShort }}
                                            </p>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            <p class="match-formate mb-0 {{ $matchFormatClass }}">
                                                @if($matchFormat === 'T20')
                                                <span
                                                    class="t20-series badge text-light border">{{ $matchFormat }}</span>
                                                @elseif($matchFormat === 'TEST')
                                                <span
                                                    class="test-series badge bg-light text-dark border">{{ $matchFormat }}</span>
                                                @else
                                                <span class="badge bg-light text-dark border">{{ $matchFormat }}</span>
                                                @endif
                                            </p>

                                            <div>
                                                @if($state === 'in progress')
                                                <span class="badge bg-success">Live<span
                                                        class="animation"></span></span>
                                                @elseif($state === 'complete')
                                                <span class="badge bg-danger">Result</span>
                                                @elseif($state === 'upcoming' || $state === 'preview')
                                                <span class="badge bg-secondary">Upcoming</span>
                                                @else
                                                <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-2 opacity-25">

                                    <!-- Team 1 Row -->
                                    <div class="d-flex justify-content-between align-items-center my-2">
                                        <div class="d-flex align-items-center overflow-hidden me-2">
                                            @if($team1Img)
                                            <img class="img flex-shrink-0 me-2" src="{{ $team1Img }}"
                                                alt="{{ $team1Name }}"
                                                style="width:24px; height:24px; object-fit:contain;" />
                                            @endif
                                            <span class="{{ $team1CssClass }} text-truncate"
                                                style="font-size: 14px; font-weight: 600;">{{ $team1Name }}</span>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            @if(!empty($t1Score))
                                            <span class="score-span {{ $team1CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }}
                                                ({{ $t1Score['overs_display'] ?? $t1Score['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                            @if($matchFormat === 'TEST' && !empty($t1Score2))
                                            <br>
                                            <span class="score-span {{ $team1CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }}
                                                ({{ $t1Score2['overs_display'] ?? $t1Score2['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Team 2 Row -->
                                    <div class="d-flex justify-content-between align-items-center my-2">
                                        <div class="d-flex align-items-center overflow-hidden me-2">
                                            @if($team2Img)
                                            <img class="img flex-shrink-0 me-2" src="{{ $team2Img }}"
                                                alt="{{ $team2Name }}"
                                                style="width:24px; height:24px; object-fit:contain;" />
                                            @endif
                                            <span class="{{ $team2CssClass }} text-truncate"
                                                style="font-size: 14px; font-weight: 600;">{{ $team2Name }}</span>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            @if(!empty($t2Score))
                                            <span class="score-span {{ $team2CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }}
                                                ({{ $t2Score['overs_display'] ?? $t2Score['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                            @if($matchFormat === 'TEST' && !empty($t2Score2))
                                            <br>
                                            <span class="score-span {{ $team2CssClass }}"
                                                style="font-size: 13px; font-weight: 600;">
                                                {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }}
                                                ({{ $t2Score2['overs_display'] ?? $t2Score2['overs'] ?? '-' }} ovs)
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Match Status Footer -->
                                    <div class="mt-2 pt-1 border-top">
                                        @if($state === 'complete')
                                        <p class="card-text status-complete text-danger mb-0"
                                            style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                        @else
                                        <p class="card-text status-else text-muted mb-0"
                                            style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                        @endif
                                    </div>
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

<!-- Recent Tab Section -->
<section id="recent_section" style="display:{{ $activeTab == 'recent' ? 'block' : 'none' }};">
    <div class="container-fluid">
        <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
            @if(isset($error) && $activeTab == 'recent')
            <span style="color:red;">Wait ...</span>
            @elseif(isset($recentMatches) && count($recentMatches) === 0 && $activeTab == 'recent')
            <p>No recent matches found.</p>
            @elseif(isset($recentMatches) && $activeTab == 'recent')
            @php
            usort($recentMatches, function($a, $b) {
            $aDate = isset($a['matchInfo']['startDate']) ? $a['matchInfo']['startDate'] : (isset($a['startDate']) ? $a['startDate'] : 0);
            $bDate = isset($b['matchInfo']['startDate']) ? $b['matchInfo']['startDate'] : (isset($b['startDate']) ? $b['startDate'] : 0);
            return $bDate <=> $aDate;
            });
            @endphp
            @foreach($recentMatches as $match)
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
            $team1Img = (!empty($team1['imageId']) && !empty($team1Name)) ?
            'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg' : '';
            $team2Img = (!empty($team2['imageId']) && !empty($team2Name)) ?
            'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg' : '';
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
            <div class="col match-item" data-match-id="{{ $match_id }}">
                <a href="{{ url('score/' . $match_id . '/' . $team1NameSlug . '-' . $team2NameSlug) }}"
                    style="text-decoration: none; color:#141010;">
                    <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                        <div class="card shadow-sm w-100">
                            <div class="card-body p-3">
                                <!-- Header Row: Match Info, Format & State -->
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="overflow-hidden flex-grow-1">
                                        <p class="card-text text-truncate mb-0"
                                            style="color: #817373; font-size: 14px;">
                                            <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                        </p>
                                        @if(!empty($matchDateShort))
                                        <p class="card-text mb-0 mt-1" style="color: #5d6570; font-size: 12px;">
                                            Match Date: {{ $matchDateShort }}
                                        </p>
                                        @endif
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <p class="match-formate mb-0 {{ $matchFormatClass }}">
                                            @if($matchFormat === 'T20')
                                            <span class="t20-series badge text-light border">{{ $matchFormat }}</span>
                                            @elseif($matchFormat === 'TEST')
                                            <span class="test-series badge bg-light text-dark border">{{ $matchFormat }}</span>
                                            @else
                                            <span class="badge bg-light text-dark border">{{ $matchFormat }}</span>
                                            @endif
                                        </p>

                                        <div>
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
                                    </div>
                                </div>

                                <hr class="my-2 opacity-25">

                                <!-- Team 1 Row -->
                                <div class="d-flex justify-content-between align-items-center my-2">
                                    <div class="d-flex align-items-center overflow-hidden me-2">
                                        @if($team1Img)
                                        <img class="img flex-shrink-0 me-2" src="{{ $team1Img }}"
                                            alt="{{ $team1Name }}"
                                            style="width:24px; height:24px; object-fit:contain;" />
                                        @endif
                                        <span class="{{ $team1CssClass }} text-truncate"
                                            style="font-size: 14px; font-weight: 600;">{{ $team1Name }}</span>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        @if(!empty($t1Score))
                                        <span class="score-span {{ $team1CssClass }}"
                                            style="font-size: 13px; font-weight: 600;">
                                            {{ $t1Score['runs'] ?? '-' }}/{{ $t1Score['wickets'] ?? '0' }}
                                            ({{ $t1Score['overs_display'] ?? $t1Score['overs'] ?? '-' }} ovs)
                                        </span>
                                        @endif
                                        @if($matchFormat === 'TEST' && !empty($t1Score2))
                                        <br>
                                        <span class="score-span {{ $team1CssClass }}"
                                            style="font-size: 13px; font-weight: 600;">
                                            {{ $t1Score2['runs'] ?? '-' }}/{{ $t1Score2['wickets'] ?? '0' }}
                                            ({{ $t1Score2['overs_display'] ?? $t1Score2['overs'] ?? '-' }} ovs)
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Team 2 Row -->
                                <div class="d-flex justify-content-between align-items-center my-2">
                                    <div class="d-flex align-items-center overflow-hidden me-2">
                                        @if($team2Img)
                                        <img class="img flex-shrink-0 me-2" src="{{ $team2Img }}"
                                            alt="{{ $team2Name }}"
                                            style="width:24px; height:24px; object-fit:contain;" />
                                        @endif
                                        <span class="{{ $team2CssClass }} text-truncate"
                                            style="font-size: 14px; font-weight: 600;">{{ $team2Name }}</span>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        @if(!empty($t2Score))
                                        <span class="score-span {{ $team2CssClass }}"
                                            style="font-size: 13px; font-weight: 600;">
                                            {{ $t2Score['runs'] ?? '-' }}/{{ $t2Score['wickets'] ?? '0' }}
                                            ({{ $t2Score['overs_display'] ?? $t2Score['overs'] ?? '-' }} ovs)
                                        </span>
                                        @endif
                                        @if($matchFormat === 'TEST' && !empty($t2Score2))
                                        <br>
                                        <span class="score-span {{ $team2CssClass }}"
                                            style="font-size: 13px; font-weight: 600;">
                                            {{ $t2Score2['runs'] ?? '-' }}/{{ $t2Score2['wickets'] ?? '0' }}
                                            ({{ $t2Score2['overs_display'] ?? $t2Score2['overs'] ?? '-' }} ovs)
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Match Status Footer -->
                                <div class="mt-2 pt-1 border-top">
                                    @if($state === 'complete')
                                    <p class="card-text status-complete text-danger mb-0"
                                        style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                    @else
                                    <p class="card-text status-else text-muted mb-0"
                                        style="font-size: 12px; font-weight: 500;">{{ $status }}</p>
                                    @endif
                                </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('matchSlider');
    const prevBtn = document.getElementById('sliderPrevBtn');
    const nextBtn = document.getElementById('sliderNextBtn');
    
    if (slider && prevBtn && nextBtn) {
        let currentIndex = 0;
        const slideWidth = 100; // Will be calculated dynamically
        const visibleSlides = 3; // Number of slides visible at once
        
        function updateSlider() {
            const items = slider.querySelectorAll('.match-slider-item');
            if (items.length === 0) return;
            
            const itemWidth = items[0].offsetWidth + 15; // Include gap
            const containerWidth = slider.parentElement.offsetWidth;
            const maxIndex = Math.max(0, items.length - Math.floor(containerWidth / itemWidth));
            
            currentIndex = Math.min(currentIndex, maxIndex);
            const translateX = -(currentIndex * itemWidth);
            slider.style.transform = `translateX(${translateX}px)`;
            
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex >= maxIndex;
        }
        
        prevBtn.addEventListener('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        });
        
        nextBtn.addEventListener('click', function() {
            const items = slider.querySelectorAll('.match-slider-item');
            const itemWidth = items[0].offsetWidth + 15;
            const containerWidth = slider.parentElement.offsetWidth;
            const maxIndex = Math.max(0, items.length - Math.floor(containerWidth / itemWidth));
            
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateSlider();
            }
        });
        
        // Initialize and update on resize
        updateSlider();
        window.addEventListener('resize', updateSlider);
    }
});
</script>
@endsection