@extends('layouts.main')

@section('title', 'Upcoming Matches')

@section('main-container')
    <style>
        .active-tab {
            border-bottom: 2px solid #053259 !important;
        }
    </style>

    @php
        $activeType = request()->get('type');
        if (!$activeType) {
            if (request()->is('sduling/domestic')) {
                $activeType = 'domestic';
            } elseif (request()->is('sduling/league')) {
                $activeType = 'league';
            } elseif (request()->is('sduling/womens')) {
                $activeType = 'women';
            } else {
                $activeType = 'international';
            }
        }

        if (!in_array($activeType, ['international', 'domestic', 'league', 'women'])) {
            $activeType = 'international';
        }

        $schedulePayload = [];
        if ($activeType === 'international') {
            $schedulePayload = $sdulinginternational ?? [];
        } elseif ($activeType === 'domestic') {
            $schedulePayload = $sdulingdomestic ?? [];
        } elseif ($activeType === 'league') {
            $schedulePayload = $sdulingleague ?? [];
        } else {
            $schedulePayload = $sdulingwomen ?? [];
        }
    @endphp

    <div class="container-fluid main-section">
        <h3 class="text-center">Cricket Schedule - Upcoming Matches</h3>

        <div class="d-flex pb-3 flex-wrap">
            <a href="{{ url('/sduling/international?type=international') }}"
               class="btn me-2 mb-2 scoreboard-title{{ $activeType === 'international' ? ' active-tab' : '' }}">
                International
            </a>
            <a href="{{ url('/sduling/domestic?type=domestic') }}"
               class="btn me-2 mb-2 scoreboard-title{{ $activeType === 'domestic' ? ' active-tab' : '' }}">
                Domestic
            </a>
            <a href="{{ url('/sduling/league?type=league') }}"
               class="btn me-2 mb-2 scoreboard-title{{ $activeType === 'league' ? ' active-tab' : '' }}">
                League
            </a>
            <a href="{{ url('/sduling/womens?type=women') }}"
               class="btn me-2 mb-2 scoreboard-title{{ $activeType === 'women' ? ' active-tab' : '' }}">
                Women
            </a>
        </div>

        <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
            @if(!empty($errorMsg))
                <span style="color:red;">{{ $errorMsg }}</span>
            @elseif(empty($schedulePayload['matchScheduleMap']) || !is_array($schedulePayload['matchScheduleMap']))
                <p>No upcoming matches found.</p>
            @else
                @foreach($schedulePayload['matchScheduleMap'] as $item)
                    @if(isset($item['scheduleAdWrapper']))
                        @php
                            $date = $item['scheduleAdWrapper']['date'] ?? '';
                            $matchScheduleList = $item['scheduleAdWrapper']['matchScheduleList'] ?? [];
                        @endphp

                        @if(!empty($date))
                            <div class="col-12 mb-2">
                                <h5 style="color:#053259;">{{ $date }}</h5>
                                <hr style="margin:0 0 8px 0;">
                            </div>
                        @endif

                        @foreach($matchScheduleList as $schedule)
                            @php
                                $seriesName = $schedule['seriesName'] ?? '';
                                $matchInfoList = $schedule['matchInfo'] ?? [];
                            @endphp

                            @foreach($matchInfoList as $matchInfo)
                                @php
                                    $matchId = $matchInfo['matchId'] ?? '';
                                    $matchDesc = $matchInfo['matchDesc'] ?? '';
                                    $matchFormat = strtoupper($matchInfo['matchFormat'] ?? '');
                                    $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' : ($matchFormat === 'TEST' ? 'match-format-test' : ''));
                                    $startDate = isset($matchInfo['startDate']) ? ((int)$matchInfo['startDate'] / 1000) : null;
                                    $localTime = $startDate ? date('d M, Y h:i A', $startDate) : 'Time N/A';
                                    $state = strtolower($matchInfo['state'] ?? 'upcoming');
                                    $status = $matchInfo['status'] ?? $localTime;

                                    $team1 = $matchInfo['team1'] ?? [];
                                    $team2 = $matchInfo['team2'] ?? [];
                                    $team1Name = $team1['teamName'] ?? 'Team 1';
                                    $team2Name = $team2['teamName'] ?? 'Team 2';
                                    $team1Slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
                                    $team2Slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
                                    $team1Img = '';
                                    $team2Img = '';

                                    if (!empty($team1['imageId']) && !empty($team1Name)) {
                                        $team1Img = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1Slug . '.jpg';
                                    }
                                    if (!empty($team2['imageId']) && !empty($team2Name)) {
                                        $team2Img = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2Slug . '.jpg';
                                    }

                                    $venue = $matchInfo['venueInfo'] ?? [];
                                    $venueDetail = '';
                                    if (!empty($venue)) {
                                        $venueDetail = ($venue['ground'] ?? 'Venue N/A') . ', ' . ($venue['city'] ?? '') . (!empty($venue['country']) ? ' (' . $venue['country'] . ')' : '');
                                    }
                                @endphp

                                <div class="col match-item">
                                    <a href="{{ url('score/' . $matchId . '/' . $team1Slug . '-' . $team2Slug) }}" style="text-decoration: none; color:#141010;">
                                        <div class="card h-100" style="box-shadow: 2px 2px 6px 1px #053259;">
                                            <div class="row card-body">
                                                <div class="col-7">
                                                    <p class="card-text mb-0" style="color: #817373; font-size:14px">
                                                        <strong>{{ $matchDesc }} - {{ $seriesName }}</strong>
                                                    </p>
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
                                                        <span style="margin-left:6px;">{{ $team1['teamSName'] ?? $team1Name }}</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center" style="margin-top:5px;">
                                                    <div class="d-flex align-items-center">
                                                        @if($team2Img)
                                                            <img class="img" src="{{ $team2Img }}" alt="{{ $team2Name }}" />
                                                        @endif
                                                        <span style="margin-left:6px;">{{ $team2['teamSName'] ?? $team2Name }}</span>
                                                    </div>
                                                </div>
                                                <div><br>
                                                    <!-- <p class="card-text status-else">{{ $status }}</p> -->
                                                    <p class="mb-0" style="font-size:13px;">
                                                        <i class="bi bi-clock-history"></i> {{ $localTime }}
                                                        <!-- @if(!empty($venueDetail))
                                                            <br><i class="bi bi-geo-alt"></i> {{ $venueDetail }}
                                                        @endif -->
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                @endforeach
            @endif
        </div>
    </div>
@endsection
