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
        <div class="col-12 text-danger">{{ $errorMsg }}</div>
        @elseif(empty($schedulePayload['matchScheduleMap']) || !is_array($schedulePayload['matchScheduleMap']))
        <div class="col-12">
            <p class="text-muted">No upcoming matches found.</p>
        </div>
        @else
        @foreach($schedulePayload['matchScheduleMap'] as $item)
        @if(isset($item['scheduleAdWrapper']))
        @php
        $date = $item['scheduleAdWrapper']['date'] ?? '';
        $matchScheduleList = $item['scheduleAdWrapper']['matchScheduleList'] ?? [];
        @endphp

        @if(!empty($date))
        <div class="col-12 mb-2">
            <h5 class="fw-bold mb-1" style="color:#053259;">{{ $date }}</h5>
            <hr class="my-1 opacity-25">
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
        $matchFormatClass = $matchFormat === 'ODI' ? '' : ($matchFormat === 'T20' ? 'match-format-t20' : ($matchFormat
        === 'TEST' ? 'match-format-test' : ''));

        $startDate = isset($matchInfo['startDate']) ? ((int)$matchInfo['startDate'] / 1000) : null;

        // Format date and time separately for clear display
        $formattedDate = $startDate ? date('d M, Y', $startDate) : 'Date N/A';
        $formattedTime = $startDate ? date('h:i A', $startDate) : 'Time N/A';

        $state = strtolower($matchInfo['state'] ?? 'upcoming');
        $status = $matchInfo['status'] ?? '';

        $team1 = $matchInfo['team1'] ?? [];
        $team2 = $matchInfo['team2'] ?? [];
        $team1Name = $team1['teamName'] ?? 'Team 1';
        $team2Name = $team2['teamName'] ?? 'Team 2';
        $team1Slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
        $team2Slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
        $team1Img = !empty($team1['imageId']) ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] .
        '/' . $team1Slug . '.jpg' : '';
        $team2Img = !empty($team2['imageId']) ? 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] .
        '/' . $team2Slug . '.jpg' : '';
        @endphp

        <div class="col match-item">
            <a href="{{ url('score/' . $matchId . '/' . $team1Slug . '-' . $team2Slug) }}"
                class="text-decoration-none text-dark">
                <div class="card h-100 shadow-sm border-0" style="box-shadow: 2px 2px 6px 1px #053259 !important;">
                    <div class="card-body p-3">

                        <!-- Top Row: Match Info Header -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <p class="card-text mb-0 text-truncate fw-semibold"
                                style="color: #817373; font-size: 13px;">
                                {{ $matchDesc }} - {{ $seriesName }}
                            </p>

                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <span class="match-formate {{ $matchFormatClass }}">
                                    @if($matchFormat === 'T20')
                                    <span class="t20-series">{{ $matchFormat }}</span>
                                    @elseif($matchFormat === 'TEST')
                                    <span class="test-series">{{ $matchFormat }}</span>
                                    @else
                                    <span>{{ $matchFormat }}</span>
                                    @endif
                                </span>

                                @if($state === 'in progress')
                                <span class="badge bg-success">Live</span>
                                @elseif($state === 'complete')
                                <span class="badge bg-danger">Result</span>
                                @elseif($state === 'upcoming' || $state === 'preview')
                                <span class="badge bg-secondary">Upcoming</span>
                                @else
                                <span class="badge bg-info">{{ ucfirst($state) }}</span>
                                @endif
                            </div>
                        </div>

                        <hr class="my-2 opacity-25">

                        <!-- Main Content Row: Teams (Front Left) | Date & Time (Right) -->
                        <div class="row align-items-center g-2 my-1">

                            <!-- FRONT: Team Logos & Names -->
                            <div class="col-7">
                                <div class="d-flex align-items-center mb-2 overflow-hidden">
                                    @if($team1Img)
                                    <img class="flex-shrink-0 me-2" src="{{ $team1Img }}" alt="{{ $team1Name }}"
                                        style="width: 22px; height: 22px; object-fit: contain;" />
                                    @endif
                                    <span class="fw-bold text-truncate" style="font-size: 15px;">
                                        {{ $team1['teamSName'] ?? $team1Name }}
                                    </span>
                                </div>

                                <div class="d-flex align-items-center overflow-hidden">
                                    @if($team2Img)
                                    <img class="flex-shrink-0 me-2" src="{{ $team2Img }}" alt="{{ $team2Name }}"
                                        style="width: 22px; height: 22px; object-fit: contain;" />
                                    @endif
                                    <span class="fw-bold text-truncate" style="font-size: 15px;">
                                        {{ $team2['teamSName'] ?? $team2Name }}
                                    </span>
                                </div>
                            </div>

                            <!-- RIGHT: Date & Time Display -->
                            <div class="col-5 text-end border-start ps-2">
                                <div class="text-dark fw-semibold" style="font-size: 13px;">
                                    <i class="bi bi-calendar3 text-primary me-1"></i>{{ $formattedDate }}
                                </div>
                                <div class="text-muted fw-bold mt-1" style="font-size: 13px;">
                                    <i class="bi bi-clock me-1"></i>{{ $formattedTime }}
                                </div>
                            </div>

                        </div>

                        <!-- Optional Footer: Match Status Text (if present) -->
                        @if(!empty($status))
                        <div class="mt-2 pt-1 border-top">
                            <p class="mb-0 text-primary fw-medium" style="font-size: 12px;">
                                {{ $status }}
                            </p>
                        </div>
                        @endif

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