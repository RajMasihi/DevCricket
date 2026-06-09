@extends('layouts.main')

@section('title', $statsData['appIndex']['seoTitle'] ?? 'Match Stats')

@section('main-container')
<div class="container-fluid main-section py-4">
    @php
        $activeTab = $activeTab ?? request()->get('tab', 'stats');
        <!-- $seoTitle = $statsData['seriesName'] ?? ($pointtable['seriesName'] ?? 'Series Stats'); -->
        $seriesName = $statsData['seriesName'] ?? ($pointtable['seriesName'] ?? 'Series');

if (!empty($statsData['seriesName'])) {
    $seoTitle = $seriesName . ' Stats';
} elseif (!empty($pointtable['seriesName'])) {
    $seoTitle = $seriesName . ' Point Table';
} else {
    $seoTitle = 'Series Stats';
}
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
    @endphp

    <div class="row mb-3">
        <div class="col text-center">
            <h3>{{ $seoTitle }}</h3>
        </div>
    </div>

    <div class="d-flex pb-3">
        <a href="{{ url('/stats/' . $seriesId . '/' . $seriesNameSlug . '?tab=stats') }}"
           class="btn me-2 scoreboard-title{{ $activeTab === 'stats' ? ' active-tab' : '' }}">
            Stats
        </a>
        <a href="{{ url('/point-table/' . $seriesId . '/' . $seriesNameSlug . '?tab=points') }}"
           class="btn me-2 scoreboard-title{{ $activeTab === 'points' ? ' active-tab' : '' }}">
            Point Table
        </a>
    </div>

    <section id="stats_section" style="display:{{ $activeTab === 'stats' ? 'block' : 'none' }};">
        <div class="row mb-3 justify-content-center">
            <div class="col-12 col-lg-10">
                @if(!empty($headers) && !empty($values))
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width:60px;"></th>
                                    @foreach($headers as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($values as $rowIndex => $row)
                                    @php
                                        $rowValues = $row['values'] ?? [];
                                        $playerId = $row['playerId'] ?? ($rowValues[0] ?? null);
                                    @endphp
                                    <tr>
                                        <td>{{ $rowIndex + 1 }}</td>
                                        @foreach($rowValues as $colIndex => $cell)
                                            @if($colIndex === 1 && $playerId)
                                                <td>
                                                    <a href="{{ url('/player/' . $playerId) }}" target="_blank">{{ $cell }}</a>
                                                </td>
                                            @elseif($colIndex > 0)
                                                <td>{{ $cell }}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning text-center">No statistics available.</div>
                @endif
            </div>
        </div>
    </section>

    <section id="points_section" style="display:{{ $activeTab === 'points' ? 'block' : 'none' }};">
        <div class="row mb-3 justify-content-center">
            <div class="col-12 col-lg-10">
                @if(!empty($pointsGroups) && is_array($pointsGroups))
                    @foreach($pointsGroups as $group)
                        @php
                            $groupName = $group['groupName'] ?? 'Points Table';
                            $rows = $group['pointsTableInfo'] ?? [];
                        @endphp
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">{{ $groupName }}</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Team</th>
                                                <th>Match</th>
                                                <th>Win</th>
                                                <th>Loss</th>
                                                <!-- <th>T</th> -->
                                                <th>NR</th>
                                                <th>Pts</th>
                                                <th>NRR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rows as $teamRow)
                                                @php
                                                    $teamName = $teamRow['teamName'] ?? '0';
                                                    $teamNameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $teamName), '-'));
                                                    $teamImageId = $teamRow['imageId'] ?? ($teamRow['teamImageId'] ?? null);
                                                    $teamImage = $teamRow['teamImage'] ?? '';
                                                    if (empty($teamImage) && !empty($teamImageId) && !empty($teamNameSlug)) {
                                                        $teamImage = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c' . $teamImageId . '/' . $teamNameSlug . '.jpg';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if(!empty($teamImage))
                                                                <img class="img" src="{{ $teamImage }}" alt="{{ $teamName }}">
                                                            @endif
                                                            <span>{{ $teamName }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $teamRow['matchesPlayed'] ?? '0' }}</td>
                                                    <td>{{ $teamRow['matchesWon'] ?? '0' }}</td>
                                                    <td>{{ $teamRow['matchesLost'] ?? '0' }}</td>
                                                    <!-- <td>{{ $teamRow['matchesTied'] ?? '0' }}</td> -->
                                                    <td>{{ $teamRow['noRes'] ?? '0' }}</td>
                                                    <td>{{ $teamRow['points'] ?? '0' }}</td>
                                                    <td>{{ $teamRow['nrr'] ?? '0' }}</td>
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
            </div>
        </div>
    </section>
</div>

<style>
    .active-tab {
        border-bottom: 2px solid #053259 !important;
    }
</style>
@endsection