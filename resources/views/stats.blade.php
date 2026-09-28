@extends('layouts.main')

@section('title', $pageTitle ?? 'Series Stats - Criclivem')

@section('meta-description')
    <meta name="description" content="{{ $metaDescription ?? 'View cricket series statistics including most runs, most wickets, highest scores, and bowling figures.' }}">
@endsection

@section('meta-keywords')
    <meta name="keywords" content="{{ $metaKeywords ?? 'cricket series stats, cricket statistics, most runs, most wickets, cricket records, series performance' }}">
@endsection

@section('main-container')
<style>
.stats-page-container {
    background: #f8f9fa;
    min-height: 100vh;
}

.stats-sidebar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    height: fit-content;
}

.stats-sidebar h4 {
    color: #053259;
    font-weight: 700;
    margin-bottom: 15px;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-category {
    margin-bottom: 25px;
}

.stats-category-title {
    color: #053259;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #053259;
    padding-bottom: 5px;
}

.stats-nav-item {
    display: block;
    padding: 10px 15px;
    color: #495057;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
}

.stats-nav-item:hover {
    background: #f8f9fa;
    color: #053259;
    transform: translateX(5px);
}

.stats-nav-item.active {
    background: #053259;
    color: white;
    font-weight: 600;
}

.stats-main-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 25px;
}

.stats-header {
    background: linear-gradient(135deg, #053259 0%, #0a4d8c 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(5, 50, 89, 0.2);
}

.stats-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.stats-header p {
    margin: 5px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
}

.format-filter {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.format-btn {
    padding: 8px 20px;
    border: 2px solid #053259;
    background: white;
    color: #053259;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.format-btn:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
}

.format-btn.active {
    background: #053259;
    color: white;
    border-color: #053259;
    box-shadow: 0 4px 12px rgba(5, 50, 89, 0.3);
}

.stats-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.stats-table thead {
    background: #f8f9fa;
}

.stats-table th {
    padding: 15px;
    font-weight: 600;
    color: #053259;
    border-bottom: 2px solid #053259;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-table td {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
    font-size: 14px;
}

.stats-table tbody tr:hover {
    background: #f8f9fa;
}

.rank-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    font-weight: 700;
    font-size: 14px;
}

.rank-number.top-3 {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color: #053259;
}

.rank-number.rank-1 {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color: #053259;
    width: 40px;
    height: 40px;
    font-size: 16px;
}

.rank-number.rank-2 {
    background: linear-gradient(135deg, #c0c0c0 0%, #e0e0e0 100%);
    color: #053259;
}

.rank-number.rank-3 {
    background: linear-gradient(135deg, #cd7f32 0%, #e6a15c 100%);
    color: white;
}

.rank-number.other {
    background: #f8f9fa;
    color: #495057;
}

.player-link {
    color: #053259;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.player-link:hover {
    color: #0a4d8c;
}

@media (max-width: 768px) {
    .stats-sidebar {
        margin-bottom: 20px;
    }
    
    .stats-main-content {
        padding: 15px;
    }
    
    .stats-header {
        padding: 15px;
    }
    
    .stats-header h2 {
        font-size: 20px;
    }
}
</style>

<div class="container-fluid main-section py-4">
    @php
        $activeTab = $activeTab ?? request()->get('tab', 'stats');
        $activeStatType = request()->get('stat_type', 'most_runs');
        $activeFormat = request()->get('format', 'odi');

        // Get series name for display
        $displaySeriesName = $statsData['seriesName'] ?? ($pointtable['seriesName'] ?? 'Series');

        $seriesId = request()->route('id');
   
        $seriesNameSlug = request()->route('seriesname') ?? 'series';

        $statsListKey = null;
        foreach (($statsData ?? []) as $key => $value) {
            if (str_ends_with($key, 'StatsList') && is_array($value)) {
                $statsListKey = $key;
                break;
            }
        }

        $headers = $statsListKey ? ($statsData[$statsListKey]['headers'] ?? []) : [];
        $values = $statsListKey ? ($statsData[$statsListKey]['values'] ?? []) : [];
        $pointsGroups = $pointtable['pointsTable'] ?? [];
        
        // Define stat types
        $statTypes = [
            'most_runs' => 'Most Runs',
            'highest_scores' => 'Highest Scores',
            'best_batting_avg' => 'Best Batting Average',
            'best_batting_sr' => 'Best Batting Strike Rate',
            'most_hundreds' => 'Most Hundreds',
            'most_fifties' => 'Most Fifties',
            'most_fours' => 'Most Fours',
            'most_sixes' => 'Most Sixes',
            'most_nineties' => 'Most Nineties',
            'most_wickets' => 'Most Wickets',
            'best_bowling_avg' => 'Best Bowling Average',
            'best_bowling' => 'Best Bowling',
            'most_5w' => 'Most 5 Wickets Haul',
            'best_economy' => 'Best Economy',
            'best_bowling_sr' => 'Best Bowling Strike Rate'
        ];
        
        // Define formats
        $formats = ['odi' => 'ODI', 'test' => 'TEST', 't20' => 'T20'];
    @endphp

    <div class="stats-page-container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="stats-sidebar">
                    <h4>Statistics</h4>
                    
                    <!-- Batting Category -->
                    <div class="stats-category">
                        <div class="stats-category-title">Batting</div>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=most_runs&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'most_runs' ? 'active' : '' }}">
                            Most Runs
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=highest_scores&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'highest_scores' ? 'active' : '' }}">
                            Highest Scores
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=best_batting_avg&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'best_batting_avg' ? 'active' : '' }}">
                            Best Batting Average
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=best_batting_sr&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'best_batting_sr' ? 'active' : '' }}">
                            Best Batting Strike Rate
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=most_hundreds&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'most_hundreds' ? 'active' : '' }}">
                            Most Hundreds
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=most_fifties&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'most_fifties' ? 'active' : '' }}">
                            Most Fifties
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=most_fours&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'most_fours' ? 'active' : '' }}">
                            Most Fours
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=most_sixes&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'most_sixes' ? 'active' : '' }}">
                            Most Sixes
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=most_nineties&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'most_nineties' ? 'active' : '' }}">
                            Most Nineties
                        </a>
                    </div>
                    
                    <!-- Bowling Category -->
                    <div class="stats-category">
                        <div class="stats-category-title">Bowling</div>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=most_wickets&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'most_wickets' ? 'active' : '' }}">
                            Most Wickets
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=best_bowling_avg&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'best_bowling_avg' ? 'active' : '' }}">
                            Best Bowling Average
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=best_bowling&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'best_bowling' ? 'active' : '' }}">
                            Best Bowling
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=most_5w&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'most_5w' ? 'active' : '' }}">
                            Most 5 Wickets Haul
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=best_economy&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'best_economy' ? 'active' : '' }}">
                            Best Economy
                        </a>
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=best_bowling_sr&format=' . $activeFormat) }}" 
                           class="stats-nav-item {{ $activeStatType === 'best_bowling_sr' ? 'active' : '' }}">
                            Best Bowling Strike Rate
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <div class="stats-main-content">
                    <!-- Header -->
                    <div class="stats-header">
                        <h2>{{ $displaySeriesName }} Statistics</h2>
                        <p>{{ $statTypes[$activeStatType] ?? 'Most Runs' }} - {{ strtoupper($activeFormat) }}</p>
                    </div>
                    
                    <!-- Format Filter -->
                    <div class="format-filter">
                        @foreach($formats as $formatKey => $formatLabel)
                            <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=' . $activeStatType . '&format=' . $formatKey) }}" 
                               class="format-btn {{ $activeFormat === $formatKey ? 'active' : '' }}">
                                {{ $formatLabel }}
                            </a>
                        @endforeach
                    </div>
                    
                    <!-- Stats Table -->
                    <section id="stats_section" style="display:{{ $activeTab === 'stats' ? 'block' : 'none' }};">
                        @if(!empty($headers) && !empty($values))
                            <div class="table-responsive">
                                <table class="stats-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 60px;">#</th>
                                            @foreach($headers as $header)
                                                <th class="{{ $loop->first ? 'text-start' : 'text-end' }}">{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($values as $rowIndex => $row)
                                            @php
                                                $rowValues = $row['values'] ?? [];
                                                $playerId = $row['playerId'] ?? ($rowValues[0] ?? null);
                                                $rankClass = 'other';
                                                if ($rowIndex == 0) $rankClass = 'rank-1';
                                                elseif ($rowIndex == 1) $rankClass = 'rank-2';
                                                elseif ($rowIndex == 2) $rankClass = 'rank-3';
                                                elseif ($rowIndex < 3) $rankClass = 'top-3';
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <span class="rank-number {{ $rankClass }}">{{ $rowIndex + 1 }}</span>
                                                </td>
                                                @foreach($rowValues as $colIndex => $cell)
                                                    @if($colIndex === 1 && $playerId)
                                                        <td class="text-start">
                                                            <a href="{{ url('/live-cricket-score/' . $playerId) }}" class="player-link">{{ $cell }}</a>
                                                        </td>
                                                    @elseif($colIndex > 0)
                                                        <td class="text-end font-monospace fw-semibold">{{ $cell }}</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning text-center">No statistics available for this category.</div>
                        @endif
                    </section>
                    
                    <!-- Point Table Section -->
                    <section id="points_section" style="display:{{ $activeTab === 'points' ? 'block' : 'none' }};">
                        @if(!empty($pointsGroups) && is_array($pointsGroups))
                            @foreach($pointsGroups as $group)
                                @php
                                    $groupName = $group['groupName'] ?? 'Points Table';
                                    $rows = $group['pointsTableInfo'] ?? [];
                                @endphp
                                <div class="card mb-3 border-0 shadow-sm overflow-hidden">
                                    <div class="card-header bg-dark text-white fw-bold py-3 px-4">{{ $groupName }}</div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="stats-table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-start" style="min-width: 140px;">Team</th>
                                                        <th class="text-end">M</th>
                                                        <th class="text-end">W</th>
                                                        <th class="text-end">L</th>
                                                        <th class="text-end">NR</th>
                                                        <th class="text-end fw-bold">Pts</th>
                                                        <th class="text-end">NRR</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($rows as $teamRow)
                                                        @php
                                                            $teamName = $teamRow['teamName'] ?? 'N/A';
                                                            $teamNameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $teamName), '-'));
                                                            $teamImageId = $teamRow['imageId'] ?? ($teamRow['teamImageId'] ?? null);
                                                            $teamImage = $teamRow['teamImage'] ?? '';
                                                            if (empty($teamImage) && !empty($teamImageId) && !empty($teamNameSlug)) {
                                                                $teamImage = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $teamImageId . '/' . $teamNameSlug . '.jpg';
                                                            }
                                                        @endphp
                                                        <tr>
                                                            <td class="text-start">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    @if(!empty($teamImage))
                                                                        <img class="rounded-circle" src="{{ $teamImage }}" alt="{{ $teamName }}" width="24" height="24">
                                                                    @endif
                                                                    <span class="fw-semibold text-dark">{{ $teamName }}</span>
                                                                </div>
                                                            </td>
                                                            <td class="text-end font-monospace">{{ $teamRow['matchesPlayed'] ?? '0' }}</td>
                                                            <td class="text-end font-monospace">{{ $teamRow['matchesWon'] ?? '0' }}</td>
                                                            <td class="text-end font-monospace">{{ $teamRow['matchesLost'] ?? '0' }}</td>
                                                            <td class="text-end font-monospace">{{ $teamRow['noRes'] ?? '0' }}</td>
                                                            <td class="text-end font-monospace fw-bold text-primary">{{ $teamRow['points'] ?? '0' }}</td>
                                                            <td class="text-end font-monospace">{{ $teamRow['nrr'] ?? '0' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning text-center">No point table data available.</div>
                        @endif
                    </section>
                    
                    <!-- Tab Navigation -->
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ url('/series-stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats&stat_type=' . $activeStatType . '&format=' . $activeFormat) }}"
                           class="btn btn-primary {{ $activeTab === 'stats' ? 'active' : '' }}">
                            Statistics
                        </a>
                        @if(!empty($pointsGroups) && is_array($pointsGroups))
                        <a href="{{ url('/point-table/' . $seriesId . '/' . $seriesNameSlug . '?tab=points') }}"
                           class="btn btn-outline-primary {{ $activeTab === 'points' ? 'active' : '' }}">
                            Point Table
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection