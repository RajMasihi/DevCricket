<?php
use Illuminate\Support\Str;
?>
@extends('layouts.main')
@section('title', $serieslists['seriesName'] ?? $serieslists['name'] ?? 'Series Matches')
@section('main-container')
    <div class="container-fluit main-section">
        <h4 style="text-align: center; font-size:28px">
            Series Full Matches: <span>@if(isset($serieslists['seriesName'])){{ $serieslists['seriesName'] ?? $serieslists['name'] ?? '' }}</span>
            @else
                IPL 2026
            @endif
        </h4>
        @php
            $seriesId = request()->route('id') ?? ($serieslists['seriesId'] ?? '');
            $seriesNameForSlug = $serieslists['seriesName'] ?? $serieslists['name'] ?? request()->route('seriesname') ?? '';
            $seriesNameSlug = request()->route('seriesname') ?? Str::slug($seriesNameForSlug);
        @endphp
        <a href="{{ url('/point-table/' . $seriesId . '/' . $seriesNameSlug) }}"
               class="btn me-2 scoreboard-title point-table-nav"
               type="button">
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
                    $matchDate = \Carbon\Carbon::createFromTimestampMs($match['startDate'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
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
                {{-- Show today's matches first  .'/'.$team1NameSlug.'-vs-'.$team2NameSlug --}}
                @foreach (array_merge($todayMatches, $otherMatches) as $match)
                    <div class='col match-item'>
                    @php
                        // Use for both API types as in the index
                        $seriesname = $serieslists['seriesName'] ?? $serieslists['name'] ?? '';
                        $ser = explode(', ', $seriesname);
                        $new_sername = $ser[1] ?? $seriesname;

                        // Team 1
                        $team1 = $match['team1'] ?? [];
                        $team1Name = $team1['teamName'] ?? ($match['teamInfo'][0]['name'] ?? '');
                        $team1Short = $team1['teamSName'] ?? ($match['teamInfo'][0]['shortname'] ?? '');
                        $team1NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team1Name), '-'));
                        $team1Img = $defaultImg;
                        if (!empty($team1['imageId']) && !empty($team1Name)) {
                            $team1Img = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team1['imageId'] . '/' . $team1NameSlug . '.jpg';
                        }
                        if (empty($team1['imageId']) && !empty($match['teamInfo'][0]['img'])) {
                            $team1Img = $match['teamInfo'][0]['img'];
                        }
                        $score1 = '';
                        if (isset($match['matchScore']['team1Score']['inngs1'])) {
                            $i = $match['matchScore']['team1Score']['inngs1'];
                            $runs = isset($i['runs']) ? $i['runs'] : '--';
                            $wickets = isset($i['wickets']) ? $i['wickets'] : '--';
                            $overs = isset($i['overs']) ? $i['overs'] : '--';
                            $score1 = "{$runs}/{$wickets} ({$overs})";
                        }

                        // Team 2
                        $team2 = $match['team2'] ?? [];
                        $team2Name = $team2['teamName'] ?? ($match['teamInfo'][1]['name'] ?? '');
                        $team2Short = $team2['teamSName'] ?? ($match['teamInfo'][1]['shortname'] ?? '');
                        $team2NameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $team2Name), '-'));
                        $team2Img = $defaultImg;
                        if (!empty($team2['imageId']) && !empty($team2Name)) {
                            $team2Img = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $team2['imageId'] . '/' . $team2NameSlug . '.jpg';
                        }
                        if (empty($team2['imageId']) && !empty($match['teamInfo'][1]['img'])) {
                            $team2Img = $match['teamInfo'][1]['img'];
                        }
                        $score2 = '';
                        if (isset($match['matchScore']['team2Score']['inngs1'])) {
                            $i = $match['matchScore']['team2Score']['inngs1'];
                            $runs = isset($i['runs']) ? $i['runs'] : '--';
                            $wickets = isset($i['wickets']) ? $i['wickets'] : '--';
                            $overs = isset($i['overs']) ? $i['overs'] : '--';
                            $score2 = "{$runs}/{$wickets} ({$overs})";
                        }

                        $matchId = $match['matchId'] ?? '';
                    @endphp
                        <a href="{{ url('score/' . $matchId . '/' . ($team1NameSlug ?: 'team-1') . '-vs-' . ($team2NameSlug ?: 'team-2'))}}" style="text-decoration: none; color:#141010;">
                            <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                                <div class='row card-body'>
                                    

                                    <p class='card-text col-8' style="color: #817373">
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
                                    </p>
                                    <p class="match-formate col-2" style='margin-top:-15px;'>
                                        @php
                                            $format = $match['matchFormat'] ?? $match['matchType'] ?? '';
                                        @endphp
                                        @if ($format)
                                            @if (strtolower($format) === 'odi')
                                                <span>ODI</span>
                                            @elseif (strtolower($format) === 't20')
                                                <span class="t20-series">T20</span>
                                            @elseif (strtolower($format) === 'test')
                                                <span class="test-series">Test</span>
                                            @endif
                                        @endif
                                    </p>
                                    <div class="col-6">
                                        <span class='card-title'>
                                            <img class="img" src="{{ $team1Img }}" alt="{{ $team1Name ?: 'No Image' }}" />
                                            &nbsp; {{ $team1Name }}
                                        </span><br>
                                        <span class='card-title'>
                                            <img class="img" src="{{ $team2Img }}" alt="{{ $team2Name ?: 'No Image' }}" />
                                            &nbsp; {{ $team2Name }}
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <p class='card-text text-align-right'>
                                            @php
                                                $hasScore = !empty($score1) || !empty($score2);
                                                if ($hasScore) {
                                                    if (!empty($score1)) {
                                                        echo $score1;
                                                    }
                                                    if (!empty($score1) && !empty($score2)) {
                                                        echo "<br>";
                                                    }
                                                    if (!empty($score2)) {
                                                        echo $score2;
                                                    }
                                                } else {
                                                    // Show match date/time as in index
                                                    if (isset($match['startDate'])) {
                                                        $startLocal = \Carbon\Carbon::createFromTimestampMs($match['startDate'])->setTimezone('Asia/Kolkata');
                                                        $startGMT = \Carbon\Carbon::createFromTimestampMs($match['startDate'])->setTimezone('GMT');
                                                        echo $startLocal->format('M j') . ' &nbsp;' .
                                                             $startGMT->format('h:i A') . '(GMT)/<b>' .
                                                             $startLocal->format('h:i A') . '</b>(Local)';
                                                    } elseif (isset($match['dateTimeGMT'])) {
                                                        $dateTimeGMT = new DateTime($match['dateTimeGMT'], new DateTimeZone('GMT'));
                                                        $dateLocal = new DateTime($match['dateTimeGMT']);
                                                        $dateTimeGMT->setTimezone(new DateTimeZone('Asia/Kolkata'));
                                                        $seriesdate = isset($match['date']) ? strtotime($match['date']) : '';
                                                        echo ($seriesdate ? date('M j', $seriesdate) : '') .
                                                            ' &nbsp;' .
                                                            ($dateLocal ? $dateLocal->format('h:i A') : '') .
                                                            '(GMT)/' . '<b>' .
                                                            $dateTimeGMT->format('h:i A') . '</b>' .
                                                            '(Local)';
                                                    }
                                                }
                                            @endphp
                                        </p>
                                    </div>
                                    @if ((isset($match['state']) && strtolower($match['state']) === 'complete') || ($match['matchEnded'] ?? false))
                                        <p class='card-text' style='color:#de280c'>{{ $match['status'] ?? 'Match Complete' }}</p>
                                    @else
                                        <p class='card-text' style='color:#0c7cde'>{{ $match['status'] ?? '' }}</p>
                                    @endif
                                    @php
                                        if (isset($match['startDate'])) {
                                            $matchDateTodayCheck = \Carbon\Carbon::createFromTimestampMs($match['startDate'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
                                        } elseif (isset($match['dateTimeGMT'])) {
                                            $matchDateTodayCheck = \Carbon\Carbon::parse($match['dateTimeGMT'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
                                        } else {
                                            $matchDateTodayCheck = '';
                                        }
                                    @endphp
                                    @if($matchDateTodayCheck === $today)
                                        <span class="badge bg-success" style="margin-top:8px;">Today's Match</span>
                                    @endif
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
