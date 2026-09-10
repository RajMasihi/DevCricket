@extends('layouts.main')

@section('title', $statsData['appIndex']['seoTitle'] ?? 'Match Stats')

@section('main-container')
<div class="container-fluid main-section py-4">
    @php
        $activeTab = $activeTab ?? request()->get('tab', 'stats');

        // $seoTitle = $statsData['seriesName'] ?? ($pointtable['seriesName'] ?? 'Series Stats');
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
        @if(!empty($pointsGroups) && is_array($pointsGroups))
        <a href="{{ url('/point-table/' . $seriesId . '/' . $seriesNameSlug . '?tab=points') }}"
           class="btn me-2 scoreboard-title{{ $activeTab === 'points' ? ' active-tab' : '' }}">
            Point Table
        </a>
        @endif
    </div>

    <section id="stats_section" style="display:{{ $activeTab === 'stats' ? 'block' : 'none' }};">
        <div class="row mb-3 justify-content-center">
            <div class="col-12 col-lg-10 p-0 p-sm-2">
                @if(!empty($headers) && !empty($values))
                    <div class="table-responsive cb-responsive-table">
                        <table class="table table-hover align-middle custom-cb-table text-nowrap mb-0" style="min-width: 290px;">
                            <!-- Header & Body structure remains identical -->
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
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
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted small fw-semibold">{{ $rowIndex + 1 }}</td>
                                        @foreach($rowValues as $colIndex => $cell)
                                            @if($colIndex === 1 && $playerId)
                                                <td class="text-start text-truncate" style="max-width: 180px;">
                                                    <a href="{{ url('/player/' . $playerId) }}" class="player-link fw-bold" target="_blank">{{ $cell }}</a>
                                                </td>
                                            @elseif($colIndex > 0)
                                                <td class="text-end font-monospace">{{ $cell }}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning text-center m-3">No statistics available.</div>
                @endif
            </div>
        </div>
    </section>

    <section id="points_section" style="display:{{ $activeTab === 'points' ? 'block' : 'none' }};">
        <div class="row mb-3 justify-content-center">
            <div class="col-12 col-lg-10 p-0 p-sm-2">
                @if(!empty($pointsGroups) && is_array($pointsGroups))
                    @foreach($pointsGroups as $group)
                        @php
                            $groupName = $group['groupName'] ?? 'Points Table';
                            $rows = $group['pointsTableInfo'] ?? [];
                        @endphp
                        <div class="card mb-3 border-0 rounded-0 rounded-sm-3 shadow-sm overflow-hidden">
                            <div class="card-header bg-dark text-white fw-bold py-2 px-3">{{ $groupName }}</div>
                            <div class="card-body p-0">
                                <div class="table-responsive cb-responsive-table">
                                    <table class="table table-hover align-middle custom-cb-table text-nowrap mb-0" style="min-width: 290px;">
                                        <!-- Header & Body structure remains identical -->
                                        <thead class="table-light text-secondary">
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
                                                                <img class="team-flag-img rounded-circle" src="{{ $teamImage }}" alt="{{ $teamName }}" width="24" height="24">
                                                            @endif
                                                            <span class="fw-semibold text-dark text-truncate" style="max-width: 130px;">{{ $teamName }}</span>
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
                    <div class="alert alert-warning text-center m-3">No point table data available.</div>
                @endif
            </div>
        </div>
    </section>
</div>

@endsection