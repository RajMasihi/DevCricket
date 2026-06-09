
@extends('layouts.main')

@section('title', $appIndex['seoTitle'] ?? 'Teams')

@section('main-container')
<div class="container-fluid main-section">
    <!-- Tab buttons for team sections -->
    <div class="d-flex pb-3">
        <a href="{{ url('/teams/international') }}" id="international_btn" class="btn me-2 scoreboard-title{{ request()->is('teams/international') || request()->is('teams') ? ' active-tab' : '' }}" type="button">International</a>
        <a href="{{ url('/teams/domestic') }}" id="domestic_btn" class="btn me-2 scoreboard-title{{ request()->is('teams/domestic') ? ' active-tab' : '' }}" type="button">Domestic</a>
        <a href="{{ url('/teams/womens') }}" id="womens_btn" class="btn me-2 scoreboard-title{{ request()->is('teams/womens') ? ' active-tab' : '' }}" type="button">Womens</a>
        <a href="{{ url('/teams/league') }}" id="league_btn" class="btn scoreboard-title{{ request()->is('teams/league') ? ' active-tab' : '' }}" type="button">League</a>
    </div>

    <!-- International Teams Tab -->
    @if(request()->is('teams/international') || request()->is('teams'))
        <div class="alert alert-info mt-4 mb-0 text-center">
        <div class="container my-4">
            <h4 class="mb-4 text-center">International Teams</h4>
            <div class="row justify-content-center">
                @php
                    $teamGroups = $teamsinternational['list'] ?? [];
                @endphp
                @if(!empty($teamGroups))
                    @foreach($teamGroups as $team)
                        {{-- If this array entry is a section header (like Test Teams, Associate Teams) --}}
                        @if(isset($team['teamName']) && !isset($team['teamId']))
                            <div class="col-12 text-start mt-4 mb-2">
                                <h5>{{ $team['teamName'] }}</h5>
                                <hr>
                            </div>
                        @elseif(isset($team['teamId']))
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body text-center">
                                        @if(isset($team['imageId']))
                                            <img src="https://static.cricbuzz.com/a/img/v1/60x60/i1/c{{ $team['imageId'] }}/{{ \Illuminate\Support\Str::slug($team['teamName']) }}.jpg"
                                                alt="{{ $team['teamName'] }}"
                                                style="height:48px;max-width:48px;object-fit:contain;margin-bottom:10px; border-radius:50%">
                                        @endif
                                        <h6 class="mb-1">{{ $team['teamName'] }}</h6>
                                        <span class="text-muted">{{ $team['teamSName'] ?? '' }}</span>
                                        @if(isset($team['countryName']))
                                            <div>
                                                <small class="text-secondary">{{ $team['countryName'] }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-12">
                        <p>No International Team found.</p>
                    </div>
                @endif
            </div>
        </div>
        </div>
    @elseif(request()->is('teams/domestic'))
        <div class="alert alert-info mt-4 mb-0 text-center">
        <div class="container my-4">
            <h4 class="mb-4 text-center">Domestic Teams</h4>
            <div class="row justify-content-center">
                @php
                    $teamGroups = $teamsDomestic['list'] ?? [];
                @endphp
                @if(!empty($teamGroups))
                    @foreach($teamGroups as $team)
                        {{-- If this array entry is a section header (like Test Teams, Associate Teams) --}}
                        @if(isset($team['teamName']) && !isset($team['teamId']))
                            <div class="col-12 text-start mt-4 mb-2">
                                <h5>{{ $team['teamName'] }}</h5>
                                <hr>
                            </div>
                        @elseif(isset($team['teamId']))
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body text-center">
                                        @if(isset($team['imageId']))
                                            <img src="https://static.cricbuzz.com/a/img/v1/60x60/i1/c{{ $team['imageId'] }}/{{ \Illuminate\Support\Str::slug($team['teamName']) }}.jpg"
                                                alt="{{ $team['teamName'] }}"
                                                style="height:48px;max-width:48px;object-fit:contain;margin-bottom:10px; border-radius:50%">
                                        @endif
                                        <h6 class="mb-1">{{ $team['teamName'] }}</h6>
                                        <span class="text-muted">{{ $team['teamSName'] ?? '' }}</span>
                                        @if(isset($team['countryName']))
                                            <div>
                                                <small class="text-secondary">{{ $team['countryName'] }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-12">
                        <p>No Domestic Teams found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
        </div>
    @elseif(request()->is('teams/womens'))
        <div class="alert alert-info mt-4 mb-0 text-center">
        <div class="container my-4">
            <h4 class="mb-4 text-center">Womens Teams</h4>
            <div class="row justify-content-center">
                @php
                    $teamGroups = $teamsWomens['list'] ?? [];
                @endphp
                @if(!empty($teamGroups))
                    @foreach($teamGroups as $team)
                        {{-- If this array entry is a section header (like Test Teams, Associate Teams) --}}
                        @if(isset($team['teamName']) && !isset($team['teamId']))
                            <div class="col-12 text-start mt-4 mb-2">
                                <h5>{{ $team['teamName'] }}</h5>
                                <hr>
                            </div>
                        @elseif(isset($team['teamId']))
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body text-center">
                                        @if(isset($team['imageId']))
                                            <img src="https://static.cricbuzz.com/a/img/v1/60x60/i1/c{{ $team['imageId'] }}/{{ \Illuminate\Support\Str::slug($team['teamName']) }}.jpg"
                                                alt="{{ $team['teamName'] }}"
                                                style="height:48px;max-width:48px;object-fit:contain;margin-bottom:10px; border-radius:50%">
                                        @endif
                                        <h6 class="mb-1">{{ $team['teamName'] }}</h6>
                                        <span class="text-muted">{{ $team['teamSName'] ?? '' }}</span>
                                        @if(isset($team['countryName']))
                                            <div>
                                                <small class="text-secondary">{{ $team['countryName'] }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-12">
                        <p>No Womens Teams found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
        </div>
    @elseif(request()->is('teams/league'))
        <div class="alert alert-info mt-4 mb-0 text-center">
        <div class="container my-4">
            <h4 class="mb-4 text-center">League Teams</h4>
            <div class="row justify-content-center">
                @php
                    $teamGroups = $teamsleague['list'] ?? [];
                @endphp
                @if(!empty($teamGroups))
                    @foreach($teamGroups as $team)
                        {{-- If this array entry is a section header (like Test Teams, Associate Teams) --}}
                        @if(isset($team['teamName']) && !isset($team['teamId']))
                            <div class="col-12 text-start mt-4 mb-2">
                                <h5>{{ $team['teamName'] }}</h5>
                                <hr>
                            </div>
                        @elseif(isset($team['teamId']))
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body text-center">
                                        @if(isset($team['imageId']))
                                            <img src="https://static.cricbuzz.com/a/img/v1/60x60/i1/c{{ $team['imageId'] }}/{{ \Illuminate\Support\Str::slug($team['teamName']) }}.jpg"
                                                alt="{{ $team['teamName'] }}"
                                                style="height:48px;max-width:48px;object-fit:contain;margin-bottom:10px; border-radius:50%">
                                        @endif
                                        <h6 class="mb-1">{{ $team['teamName'] }}</h6>
                                        <span class="text-muted">{{ $team['teamSName'] ?? '' }}</span>
                                        @if(isset($team['countryName']))
                                            <div>
                                                <small class="text-secondary">{{ $team['countryName'] }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-12">
                        <p>No League Teams found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
        </div>
    @endif
</div>
<style>
    .active-tab {
        border-bottom: 2px solid #053259 !important;
    }
    .card img {
        max-width: 60px;
        max-height: 60px;
        margin-bottom: 8px;
    }
</style>
@endsection