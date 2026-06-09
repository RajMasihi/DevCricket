@extends('layouts.main')

@section('title', 'ICC Rankings')

@section('main-container')
    <style>
        .active-ranking-tab {
            border-bottom: 2px solid #053259 !important;
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
        <h4 class="text-center mb-3">ICC {{ $activeGender === 'mens' ? "Men's" : "Women's" }} Cricket Rankings</h4>

        <div class="d-flex flex-wrap pb-3 gap-2 d-none">
            @foreach($genderTabs as $genderKey => $genderLabel)
                <a href="{{ url('/icc-ranking/' . $genderKey . '/' . $activeCategory . '/' . $activeFormat) }}"
                   class="btn scoreboard-title {{ $activeGender === $genderKey ? 'active-ranking-tab' : '' }}">
                    {{ $genderLabel }}
                </a>
            @endforeach
        </div>

        <div class="d-flex flex-wrap pb-3 gap-2">
            @foreach($typeTabs as $tabKey => $tabLabel)
                <a href="{{ url('/icc-ranking/' . $activeGender . '/' . $tabKey . '/' . $activeFormat) }}"
                   class="btn scoreboard-title {{ $activeCategory === $tabKey ? 'active-ranking-tab' : '' }}">
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        <div class="d-flex flex-wrap pb-3 gap-2">
            @foreach($formatTabs as $formatKey => $formatLabel)
                <a href="{{ url('/icc-ranking/' . $activeGender . '/' . $activeCategory . '/' . $formatKey) }}"
                   class="btn scoreboard-title {{ $activeFormat === $formatKey ? 'active-ranking-tab' : '' }}">
                    {{ $formatLabel }}
                </a>
            @endforeach
        </div>

        @if(isset($errorMsg))
            <p class="text-danger">{{ $errorMsg }}</p>
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

            <div class="card mb-4" style="box-shadow: 2px 2px 6px 1px #053259;">
                <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                    @if(!empty($selectedImage))
                        <img src="{{ $selectedImage }}" alt="{{ $selectedName }}" style="width: 80px; height: 80px; border-radius: 50%;">
                    @endif
                    <div>
                        <h5 class="mb-1">{{ $selectedName }}</h5>
                        @if(!empty($selectedCountry))
                            <p class="mb-1 text-muted">{{ $selectedCountry }}</p>
                        @endif
                        <p class="mb-0"><strong>Rank:</strong> {{ $selectedRank }} | <strong>Points:</strong> {{ $selectedPoints }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="card" style="box-shadow: 2px 2px 6px 1px #053259;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Rank</th>
                                <th>Player Name</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($rankList) > 0)
                                @foreach($rankList as $row)
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
                                    @endphp

                                    <tr>
                                        <td class="ps-3">{{ $itemRank }}</td>
                                        <td>
                                            @if(!empty($itemId))

                                                <a href="{{ url('/icc-ranking/' . $activeGender . '/' . $activeCategory . '/' . $activeFormat . '/' . $itemId . '/' . $itemSlug) }}"
                                                   style="text-decoration:none; color:#053259;">
                                                    <img class="img" src="{{ $itemImg }}" alt="{{ $itemName ?: 'No Image' }}" />

                                                    {{ $itemName }}
                                                    <span style="font-size:15px;color:#6c757d;">( {{ $itemCountry }} )</span>
                                                </a>
                                            @else
                                                {{ $itemName }}
                                            @endif
                                        </td>
                                        <td>{{ $itemPoints }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center py-3">No ranking data found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection