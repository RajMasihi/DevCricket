@extends('layouts.main')

@section('title', $pageTitle ?? 'ICC Rankings - Criclivem')

@section('meta-description')
    <meta name="description" content="{{ $metaDescription ?? 'View latest ICC cricket rankings across all formats - Test, ODI, T20. Check player rankings for batsmen, bowlers, all-rounders, and team rankings.' }}">
@endsection

@section('meta-keywords')
    <meta name="keywords" content="{{ $metaKeywords ?? 'ICC rankings, cricket rankings, ICC player rankings, cricket team rankings, Test rankings, ODI rankings, T20 rankings' }}">
@endsection

@section('main-container')
    <style>
        .icc-rankings-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .rankings-header {
            background: linear-gradient(135deg, #053259 0%, #0a4d8c 100%);
            color: white;
            padding: 30px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(5, 50, 89, 0.3);
        }
        
        .rankings-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .rankings-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .filter-group {
            margin-bottom: 20px;
        }
        
        .filter-group:last-child {
            margin-bottom: 0;
        }
        
        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e9ecef;
            background: white;
            color: #495057;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .filter-btn:hover {
            border-color: #053259;
            color: #053259;
        }
        
        .filter-btn.active {
            background: #053259;
            color: white;
            border-color: #053259;
        }
        
        .rankings-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .rankings-table .table {
            margin-bottom: 0;
        }
        
        .rankings-table thead {
            background: #f8f9fa;
        }
        
        .rankings-table th {
            font-weight: 600;
            color: #053259;
            border-bottom: 2px solid #053259;
            padding: 15px;
            font-size: 14px;
        }
        
        .rankings-table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
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
        
        .player-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .player-image {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e9ecef;
        }
        
        .player-details {
            flex: 1;
        }
        
        .player-name {
            font-weight: 600;
            color: #053259;
            margin-bottom: 2px;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .player-name:hover {
            color: #0a4d8c;
        }
        
        .player-country {
            font-size: 12px;
            color: #6c757d;
        }
        
        .points-display {
            font-weight: 700;
            color: #053259;
            font-size: 16px;
        }
        
        .selected-player-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border: 2px solid #053259;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(5, 50, 89, 0.15);
        }
        
        .selected-player-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .selected-player-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #053259;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .selected-player-details h3 {
            margin-bottom: 5px;
            color: #053259;
        }
        
        .selected-player-details .rank-badge {
            display: inline-block;
            background: #053259;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .rankings-header {
                padding: 20px 15px;
            }
            
            .rankings-header h1 {
                font-size: 22px;
            }
            
            .filter-section {
                padding: 15px;
            }
            
            .filter-buttons {
                gap: 6px;
            }
            
            .filter-btn {
                padding: 6px 12px;
                font-size: 13px;
            }
            
            .rankings-table th,
            .rankings-table td {
                padding: 12px 10px;
                font-size: 13px;
            }
            
            .player-image {
                width: 35px;
                height: 35px;
            }
            
            .selected-player-info {
                flex-direction: column;
                text-align: center;
            }
            
            .selected-player-image {
                width: 80px;
                height: 80px;
            }
        }
    </style>

    @php
        $activeGender = $gender ?? 'mens';
        $activeCategory = $category ?? 'allrounders';
        $activeFormat = $format ?? 'test';
        $rankList = (isset($rankingData['rank']) && is_array($rankingData['rank'])) ? $rankingData['rank'] : [];
        $categoryDataKeys = [
            'batsmen' => ['batsman', 'batsmen'],
            'bowlers' => ['bowler', 'bowlers'],
            'allrounders' => ['allrounder', 'allRounder', 'allrounders'],
            'teams' => ['team', 'teams'],
        ];
        $activeDataKeys = $categoryDataKeys[$activeCategory] ?? [$activeCategory];

        $typeTabs = [
            'batsmen' => 'Batting',
            'bowlers' => 'Bowling',
            'allrounders' => 'All-Rounder',
            'teams' => 'Teams',
        ];

        $genderTabs = [
            'mens' => "Men's",
            'womens' => "Women's",
        ];

        $formatTabs = $activeGender === 'womens'
            ? [
                'odi' => 'ODI',
                't20' => 'T20',
            ]
            : [
                'test' => 'TEST',
                'odi' => 'ODI',
                't20' => 'T20',
            ];

        if (!array_key_exists($activeFormat, $formatTabs)) {
            $activeFormat = array_key_first($formatTabs);
        }
    @endphp

    <div class="container-fluid main-section">
        <div class="icc-rankings-container">
            <!-- Professional Header -->
            <div class="rankings-header">
                <h1>ICC {{ $activeGender === 'mens' ? "Men's" : "Women's" }} Rankings</h1>
                <p>Official ICC rankings for {{ $activeGender === 'mens' ? "men's" : "women's" }} cricket players and teams</p>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <!-- Gender Filter -->
                <div class="filter-group">
                    <div class="filter-label">Gender</div>
                    <div class="filter-buttons">
                        @foreach($genderTabs as $genderKey => $genderLabel)
                            @php
                                // Determine the appropriate format when switching genders
                                $targetFormat = $activeFormat;
                                $targetGenderFormats = $genderKey === 'womens' ? ['odi', 't20'] : ['test', 'odi', 't20'];
                                if (!in_array($activeFormat, $targetGenderFormats)) {
                                    $targetFormat = $genderKey === 'womens' ? 'odi' : 'test';
                                }
                            @endphp
                            <a href="{{ route('icc-rankings', ['gender' => $genderKey, 'category' => $activeCategory, 'format' => $targetFormat]) }}"
                               class="filter-btn {{ $activeGender === $genderKey ? 'active' : '' }}">
                                {{ $genderLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="filter-group">
                    <div class="filter-label">Category</div>
                    <div class="filter-buttons">
                        @foreach($typeTabs as $tabKey => $tabLabel)
                            <a href="{{ route('icc-rankings', ['gender' => $activeGender, 'category' => $tabKey, 'format' => $activeFormat]) }}"
                               class="filter-btn {{ $activeCategory === $tabKey ? 'active' : '' }}">
                                {{ $tabLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Format Filter -->
                <div class="filter-group">
                    <div class="filter-label">Format</div>
                    <div class="filter-buttons">
                        @foreach($formatTabs as $formatKey => $formatLabel)
                            <a href="{{ route('icc-rankings', ['gender' => $activeGender, 'category' => $activeCategory, 'format' => $formatKey]) }}"
                               class="filter-btn {{ $activeFormat === $formatKey ? 'active' : '' }}">
                                {{ $formatLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            @if(isset($errorMsg))
                <div class="alert alert-danger" role="alert">
                    {{ $errorMsg }}
                </div>
            @endif

            @if(!empty($selectedItem))
                @php
                    $selectedData = [];
                    foreach ($activeDataKeys as $dataKey) {
                        if (isset($selectedItem[$dataKey]) && is_array($selectedItem[$dataKey])) {
                            $selectedData = $selectedItem[$dataKey];
                            break;
                        }
                    }
                    $selectedName = $selectedData['name'] ?? $selectedData['teamName'] ?? 'Name';
                    $selectedCountry = $selectedData['country'] ?? '';
                    $selectedPoints = $selectedItem['points'] ?? '-';
                    $selectedRank = $selectedItem['rank'] ?? '-';
                    $selectedFaceImageId = $selectedData['faceImageId'] ?? '';
                    $selectedImage = !empty($selectedFaceImageId) ? 'https://static.cricbuzz.com/a/img/v1/200x200/i1/c' . $selectedFaceImageId . '/player.jpg' : '';
                @endphp

                <div class="selected-player-card">
                    <div class="selected-player-info">
                        @if(!empty($selectedImage))
                            <img src="{{ $selectedImage }}" alt="{{ $selectedName }}" class="selected-player-image">
                        @endif
                        <div class="selected-player-details">
                            <span class="rank-badge">Rank #{{ $selectedRank }}</span>
                            <h3>{{ $selectedName }}</h3>
                            @if(!empty($selectedCountry))
                                <p class="text-muted mb-2">{{ $selectedCountry }}</p>
                            @endif
                            <p class="mb-0"><strong>Rating Points:</strong> {{ $selectedPoints }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Rankings Table -->
            <div class="rankings-table">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Rank</th>
                                <th>{{ $activeCategory === 'teams' ? 'Team' : 'Player' }}</th>
                                <th style="width: 120px;">Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($rankList) > 0)
                                @foreach($rankList as $index => $row)
                                    @php
                                        $rowData = [];
                                        foreach ($activeDataKeys as $dataKey) {
                                            if (isset($row[$dataKey]) && is_array($row[$dataKey])) {
                                                $rowData = $row[$dataKey];
                                                break;
                                            }
                                        }
                                        $itemName = $row['name'] ?? $row['teamName'] ?? 'N/A';
                                        $itemCountry = $row['country'] ?? '-';
                                        $itemRank = $row['rank'] ?? '-';
                                        $itemPoints = $row['points'] ?? '-';
                                        
                                        $itemId = $row['id'] ?? $row['teamId'] ?? '';
                                        $itemImage = $row['faceImageId'] ?? $row['imageId'] ?? '';
                                        $itemName = $row['name'] ?? $row['teamName'] ?? '';
                                        $itemSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $itemName), '-'));
                                        $itemImg = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' 
                                                    . $itemImage . '/' . $itemSlug . '.jpg?d=low&p=gthumb';
                                        
                                        // Determine rank styling
                                        $rankClass = 'other';
                                        if ($itemRank == 1) $rankClass = 'rank-1';
                                        elseif ($itemRank == 2) $rankClass = 'rank-2';
                                        elseif ($itemRank == 3) $rankClass = 'rank-3';
                                        elseif ($itemRank <= 3) $rankClass = 'top-3';
                                    @endphp

                                    <tr>
                                        <td>
                                            <span class="rank-number {{ $rankClass }}">{{ $itemRank }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($itemId))
                                                <div class="player-info">
                                                    @if(!empty($itemImg))
                                                        <img src="{{ $itemImg }}" alt="{{ $itemName }}" class="player-image">
                                                    @endif
                                                    <div class="player-details">
                                                        <a href="{{ route('icc-rankings-detail', ['gender' => $activeGender, 'category' => $activeCategory, 'format' => $activeFormat, 'id' => $itemId, 'slug' => $itemSlug]) }}"
                                                           class="player-name">
                                                            {{ $itemName }}
                                                        </a>
                                                        <div class="player-country">{{ $itemCountry }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="player-info">
                                                    <div class="player-details">
                                                        <span class="player-name">{{ $itemName }}</span>
                                                        <div class="player-country">{{ $itemCountry }}</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="points-display">{{ $itemPoints }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-trophy fa-3x mb-3" style="opacity: 0.3;"></i>
                                            <p>No ranking data found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection