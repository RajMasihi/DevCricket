@extends('layouts.main')

@section('title', 'Team Details - Criclivem')

@section('meta-description', 'View detailed team information including squad, players for T20, ODI, and Test formats.')

@section('meta-keywords', 'team details, cricket squad, team players, T20 players, ODI players, Test players')

@section('main-container')
<div class="container-fluid main-section">
    @if(isset($errorMsg))
        <div class="alert alert-danger mt-4">
            <p>Error loading team details: {{ $errorMsg }}</p>
        </div>
    @elseif(isset($teamDetail) && is_array($teamDetail))
        @php
            $teamInfo = $teamDetail['team'] ?? [];
            $teamName = $teamInfo['teamName'] ?? 'Team';
            $teamId = $teamInfo['teamId'] ?? '';
            $teamImageId = $teamInfo['imageId'] ?? '';
            $teamNameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $teamName), '-'));
            $teamImage = !empty($teamImageId) ? 'https://static.cricbuzz.com/a/img/v1/200x200/i1/c' . $teamImageId . '/' . $teamNameSlug . '.jpg' : '';
            $countryName = $teamInfo['countryName'] ?? '';
        @endphp

        <div class="card mb-4" style="box-shadow: 2px 2px 6px 1px #053259;">
            <div class="card-body">
                <div class="d-flex align-items-center gap-4">
                    @if(!empty($teamImage))
                        <img src="{{ $teamImage }}" alt="{{ $teamName }} logo" style="width: 100px; height: 100px; border-radius: 50%; object-fit: contain;">
                    @endif
                    <div>
                        <h2 class="mb-1">{{ $teamName }}</h2>
                        @if(!empty($countryName))
                            <p class="text-muted mb-0">{{ $countryName }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(isset($teamDetail['players']) && is_array($teamDetail['players']) && !empty($teamDetail['players']))
            <h3 class="mb-4">Team Squad</h3>
            <div class="row">
                @foreach($teamDetail['players'] as $player)
                    @php
                        $playerName = $player['name'] ?? $player['playerName'] ?? $player['fullName'] ?? 'Unknown Player';
                        $playerId = $player['playerId'] ?? $player['id'] ?? '';
                        $playerImageId = $player['faceImageId'] ?? $player['imageId'] ?? '';
                        $playerImage = !empty($playerImageId) ? 'https://static.cricbuzz.com/a/img/v1/200x200/i1/c' . $playerImageId . '/player.jpg' : '';
                        $playerRole = $player['role'] ?? $player['playingRole'] ?? '';
                        $battingStyle = $player['battingStyle'] ?? '';
                        $bowlingStyle = $player['bowlingStyle'] ?? '';
                        $country = $player['country'] ?? $countryName;
                    @endphp
                    <div class="col-md-3 col-sm-4 col-6 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                @if(!empty($playerImage))
                                    <img src="{{ $playerImage }}" alt="{{ $playerName }}" 
                                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: contain; margin-bottom: 10px;">
                                @endif
                                <h6 class="mb-1">{{ $playerName }}</h6>
                                @if(!empty($playerRole))
                                    <span class="badge bg-info text-dark mb-1">{{ $playerRole }}</span>
                                @endif
                                @if(!empty($battingStyle))
                                    <div><small class="text-muted">Batting: {{ $battingStyle }}</small></div>
                                @endif
                                @if(!empty($bowlingStyle))
                                    <div><small class="text-muted">Bowling: {{ $bowlingStyle }}</small></div>
                                @endif
                                @if(!empty($country))
                                    <div><small class="text-secondary">{{ $country }}</small></div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <p>No player information available for this team.</p>
            </div>
        @endif

        @if(isset($teamDetail['recentMatches']) && is_array($teamDetail['recentMatches']) && !empty($teamDetail['recentMatches']))
            <h3 class="mb-4 mt-5">Recent Matches</h3>
            <div class="row">
                @foreach($teamDetail['recentMatches'] as $match)
                    @php
                        $matchInfo = $match['matchInfo'] ?? $match;
                        $matchId = $matchInfo['matchId'] ?? '';
                        $matchDesc = $matchInfo['matchDesc'] ?? 'Match';
                        $seriesName = $matchInfo['seriesName'] ?? '';
                        $status = $matchInfo['status'] ?? '';
                        $startDate = isset($matchInfo['startDate']) ? date('d M Y', (int)$matchInfo['startDate'] / 1000) : '';
                    @endphp
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-1">{{ $matchDesc }}</h6>
                                <p class="text-muted mb-1">{{ $seriesName }}</p>
                                @if(!empty($startDate))
                                    <small class="text-secondary">{{ $startDate }}</small>
                                @endif
                                @if(!empty($status))
                                    <div class="mt-2"><small class="text-info">{{ $status }}</small></div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="alert alert-warning mt-4">
            <p>No team details available.</p>
        </div>
    @endif
</div>
@endsection
