

@extends('layouts.main')
@php
    use Illuminate\Support\Str;

    $seriesId = request()->route('id') ?? ($serieslists['seriesId'] ?? '');

    // Convert route slug (e.g. "ipl-2026") into readable title if array data is missing
    $routeSeriesName = request()->route('seriesname') 
        ? ucwords(str_replace('-', ' ', request()->route('seriesname'))) 
        : null;

    // Resolve final display name with fallbacks
    $seriesDisplayName = strtoupper(
    $serieslists['seriesName']
    ?? $serieslists['name']
    ?? $routeSeriesName
    ?? 'IPL 2026'
);

    $seriesNameSlug = request()->route('seriesname') ?? Str::slug($seriesDisplayName);
@endphp
@section('title', $seriesDisplayName)

@section('main-container')
<div class="container-fluid main-section">
    <h4 class="text-center" style="font-size:28px">
        Series Full Matches: 
        <span>{{ $seriesDisplayName }}</span>
    </h4>

    
    <a href="{{ url('/point-table/' . $seriesId . '/' . $seriesNameSlug) }}"
        class="btn me-2 scoreboard-title point-table-nav" type="button">
        Point Table
    </a>
    @php
    $today = \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m-d');
    $todayMatches = [];
    $otherMatches = [];
    $matches = [];

    // Prefer modern v2 API format if present
    if (!empty($serieslists['matchDetails'])) {
    foreach ($serieslists['matchDetails'] as $detailsMap) {
    $matchesOfDay = $detailsMap['matchDetailsMap']['match'] ?? [];
    foreach ($matchesOfDay as $matchRaw) {
    $match = $matchRaw['matchInfo'] ?? [];
    // Attach scores if available (matchScore)
    if (isset($matchRaw['matchScore'])) {
    $match['matchScore'] = $matchRaw['matchScore'];
    }
    $match['dateString'] = $detailsMap['matchDetailsMap']['key'] ?? '';
    $matches[] = $match;
    }
    }
    }
    // If no v2 matches, fallback to classic format
    if (empty($matches) && !empty($serieslists['matches'])) {
    $matches = $serieslists['matches'];
    }

    // Split today and other matches
    foreach ($matches as $match) {
    if (isset($match['startDate'])) {
    $matchDate =
    \Carbon\Carbon::createFromTimestampMs($match['startDate'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
    } elseif (isset($match['dateTimeGMT'])) {
    $matchDate = \Carbon\Carbon::parse($match['dateTimeGMT'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
    } else {
    $matchDate = '';
    }
    if ($matchDate === $today) {
    $todayMatches[] = $match;
    } else {
    $otherMatches[] = $match;
    }
    }
    $defaultImg = asset('images/defult.png');
    @endphp

    @if (!empty($matches))
    <div class="row row-cols-1 row-cols-md-2 g-4 pt-3">
        {{-- Show today's matches first --}}
        @foreach (array_merge($todayMatches, $otherMatches) as $match)
        @php
        // Series Name Parsing
        $seriesname = $serieslists['seriesName'] ?? $serieslists['name'] ?? '';
        $ser = explode(', ', $seriesname);
        $new_sername = $ser[1] ?? $seriesname;

        // Team 1 Processing
        $team1 = $match['team1'] ?? [];
        $team1Name = $team1['teamName'] ?? ($match['teamInfo'][0]['name'] ?? '');
        $team1Short = $team1['teamSName'] ?? ($match['teamInfo'][0]['shortname'] ?? '');
        $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
        $team1Img = $defaultImg;
        if (!empty($team1['imageId']) && !empty($team1Name)) {
        $team1Img = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg';
        } elseif (empty($team1['imageId']) && !empty($match['teamInfo'][0]['img'])) {
        $team1Img = $match['teamInfo'][0]['img'];
        }

        $score1 = '';
        if (isset($match['matchScore']['team1Score']['inngs1'])) {
        $i = $match['matchScore']['team1Score']['inngs1'];
        $runs = $i['runs'] ?? '--';
        $wickets = $i['wickets'] ?? '--';
        $overs = $i['overs'] ?? '--';
        $score1 = "{$runs}/{$wickets} ({$overs})";
        }

        // Team 2 Processing
        $team2 = $match['team2'] ?? [];
        $team2Name = $team2['teamName'] ?? ($match['teamInfo'][1]['name'] ?? '');
        $team2Short = $team2['teamSName'] ?? ($match['teamInfo'][1]['shortname'] ?? '');
        $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
        $team2Img = $defaultImg;
        if (!empty($team2['imageId']) && !empty($team2Name)) {
        $team2Img = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg';
        } elseif (empty($team2['imageId']) && !empty($match['teamInfo'][1]['img'])) {
        $team2Img = $match['teamInfo'][1]['img'];
        }

        $score2 = '';
        if (isset($match['matchScore']['team2Score']['inngs1'])) {
        $i = $match['matchScore']['team2Score']['inngs1'];
        $runs = $i['runs'] ?? '--';
        $wickets = $i['wickets'] ?? '--';
        $overs = $i['overs'] ?? '--';
        $score2 = "{$runs}/{$wickets} ({$overs})";
        }

        $matchId = $match['matchId'] ?? '';

        // Today's Date Check
        if (isset($match['startDate'])) {
        $matchDateTodayCheck =
        \Carbon\Carbon::createFromTimestampMs($match['startDate'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
        } elseif (isset($match['dateTimeGMT'])) {
        $matchDateTodayCheck =
        \Carbon\Carbon::parse($match['dateTimeGMT'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
        } else {
        $matchDateTodayCheck = '';
        }
        @endphp

        <div class="col match-item">
            <a href="{{ url('score/' . $matchId . '/' . ($team1NameSlug ?: 'team-1') . '-vs-' . ($team2NameSlug ?: 'team-2')) }}"
                class="text-decoration-none text-dark d-block h-100">
                <div class="card h-100 shadow-sm border-0"
                    style="box-shadow: 0 4px 6px rgba(5, 50, 89, 0.15) !important;">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">

                        <!-- Header: Match Info, Venue & Format/Today's Badge -->
                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <p class="card-text text-truncate mb-0 flex-grow-1"
                                    style="color: #817373; font-size: 13px;">
                                    <strong>
                                        @if (isset($match['matchDesc']))
                                        {{ $match['matchDesc'] }}
                                        @elseif (isset($new_sername))
                                        {{ $new_sername }}
                                        @endif

                                        @if (!empty($match['venueInfo']['ground']))
                                        , {{ $match['venueInfo']['ground'] }}
                                        @elseif (!empty($match['venue']))
                                        , {{ $match['venue'] }}
                                        @endif

                                        @if (!empty($match['venueInfo']['city']))
                                        , {{ $match['venueInfo']['city'] }}
                                        @endif
                                    </strong>
                                </p>

                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                    @if($matchDateTodayCheck === $today)
                                    <span class="badge bg-success" style="font-size: 10px;">Today</span>
                                    @endif

                                    @php
                                    $format = $match['matchFormat'] ?? $match['matchType'] ?? '';
                                    @endphp
                                    @if ($format)
                                        @if (strtolower($format) === 't20')
                                            <span class="badge border match-formate" style="font-size: 11px; background-color: #424242; color:#ffff;">
                                            <span class="t20-series">T20</span>
                                        @elseif (strtolower($format) === 'odi')
                                            <span class="badge border match-formate" style="font-size: 11px; background-color:  #3D62BC; color:#ffff;">
                                            <span class="odi-series">ODI</span>
                                        @else
                                            <span class="badge text-dark border match-formate" style="font-size: 11px;">
                                            {{ strtoupper($format) }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <hr class="my-2 opacity-25">

                            <!-- Teams & Scores Grid -->
                            <div class="row align-items-center g-2 my-1">
                                <!-- Team Logistics -->
                                <div class="col-7">
                                    <div class="d-flex align-items-center mb-1 overflow-hidden">
                                        <img class="img flex-shrink-0 me-2" src="{{ $team1Img }}"
                                            alt="{{ $team1Name ?: 'Team 1' }}"
                                            style="width: 20px; height: 20px; object-fit: contain;" />
                                        <span class="fw-semibold text-truncate"
                                            style="font-size: 14px;">{{ $team1Name }}</span>
                                    </div>
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <img class="img flex-shrink-0 me-2" src="{{ $team2Img }}"
                                            alt="{{ $team2Name ?: 'Team 2' }}"
                                            style="width: 20px; height: 20px; object-fit: contain;" />
                                        <span class="fw-semibold text-truncate"
                                            style="font-size: 14px;">{{ $team2Name }}</span>
                                    </div>
                                </div>

                                <!-- Scores or Time Display -->
                                <div class="col-5 text-end">
                                    @php
                                    $hasScore = !empty($score1) || !empty($score2);
                                    @endphp

                                    @if ($hasScore)
                                    <div class="fw-bold text-nowrap" style="font-size: 13px;">
                                        <div>{{ $score1 ?: '--' }}</div>
                                        <div>{{ $score2 ?: '--' }}</div>
                                    </div>
                                    @else
                                    <div class="text-muted" style="font-size: 11px;">
                                        @if (isset($match['startDate']))
                                        @php
                                        $startLocal =
                                        \Carbon\Carbon::createFromTimestampMs($match['startDate'])->setTimezone('Asia/Kolkata');
                                        $startGMT =
                                        \Carbon\Carbon::createFromTimestampMs($match['startDate'])->setTimezone('GMT');
                                        @endphp
                                        <div>{{ $startLocal->format('M j') }}</div>
                                        <div>{{ $startGMT->format('h:i A') }} (GMT) / <strong
                                                class="text-dark">{{ $startLocal->format('h:i A') }} (Local)</strong>
                                        </div>
                                        @elseif (isset($match['dateTimeGMT']))
                                        @php
                                        $dateTimeGMT = new DateTime($match['dateTimeGMT'], new DateTimeZone('GMT'));
                                        $dateLocal = new DateTime($match['dateTimeGMT']);
                                        $dateTimeGMT->setTimezone(new DateTimeZone('Asia/Kolkata'));
                                        $seriesdate = isset($match['date']) ? strtotime($match['date']) : '';
                                        @endphp
                                        <div>{{ $seriesdate ? date('M j', $seriesdate) : '' }}</div>
                                        <div>{{ $dateLocal ? $dateLocal->format('h:i A') : '' }} (GMT) / <strong
                                                class="text-dark">{{ $dateTimeGMT->format('h:i A') }} (Local)</strong>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Footer: Match Status -->
                        <div class="mt-2 pt-2 border-top">
                            @if ((isset($match['state']) && strtolower($match['state']) === 'complete') ||
                            ($match['matchEnded'] ?? false))
                            <p class="card-text text-danger mb-0 fw-medium text-truncate" style="font-size: 12px;">
                                {{ $match['status'] ?? 'Match Complete' }}
                            </p>
                            @else
                            <p class="card-text text-primary mb-0 fw-medium text-truncate" style="font-size: 12px;">
                                {{ $match['status'] ?? '' }}
                            </p>
                            @endif
                        </div>

                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info mt-4">No matches found for this series.</div>
    @endif
</div>
@endsection