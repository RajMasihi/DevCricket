<?php
use Illuminate\Support\Str;
?>
@extends('layouts.main')

@section('title', $pageTitle ?? 'Cricket Series - International & Domestic | Criclivem')

@section('meta-description')
<meta name="description"
    content="{{ $metaDescription ?? 'Browse all cricket series including international tours, domestic leagues, and tournaments. Get complete series information, schedules, and match details.' }}">
@endsection

@section('meta-keywords')
<meta name="keywords"
    content="{{ $metaKeywords ?? 'cricket series, international cricket series, domestic cricket leagues, cricket tournaments, cricket tours, series schedule, ICC series' }}">
@endsection

@section('main-container')
<div class="container-fluid main-section">
  {{-- Category Heading Sections --}}

<div id="all_section"
     style="display: {{ ($category ?? 'all') === 'all' ? 'block' : 'none' }};">
    <h4 class="text-center mb-3">International Series</h4>
</div>

<!-- <div id="international_section"
     style="display: {{ ($category ?? 'all') === 'international' ? 'block' : 'none' }};">
    <h4 class="text-center mb-3">International Series</h4>
</div> -->

<div id="domestic_section"
     style="display: {{ ($category ?? 'all') === 'domestic' ? 'block' : 'none' }};">
    <h4 class="text-center mb-3">Domestic Series</h4>
</div>

<div id="women_section"
     style="display: {{ ($category ?? 'all') === 'women' ? 'block' : 'none' }};">
    <h4 class="text-center mb-3">Women's Series</h4>
</div>

<div id="league_section"
     style="display: {{ ($category ?? 'all') === 'league' ? 'block' : 'none' }};">
    <h4 class="text-center mb-3">League Series</h4>
</div>


{{-- Category Filter Buttons --}}

<div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">

    <a href="{{ url('/cricket-series') }}"
       class="btn series-category-btn {{ ($category ?? 'all') === 'all' ? 'active' : '' }}">
        International
    </a>

    <!-- <a href="{{ url('/cricket-series/international') }}"
       class="btn series-category-btn {{ ($category ?? 'all') === 'international' ? 'active' : '' }}">
        International
    </a> -->

    <a href="{{ url('/cricket-series/domestic') }}"
       class="btn series-category-btn {{ ($category ?? 'all') === 'domestic' ? 'active' : '' }}">
        Domestic
    </a>

    <a href="{{ url('/cricket-series/women') }}"
       class="btn series-category-btn {{ ($category ?? 'all') === 'women' ? 'active' : '' }}">
        Women's
    </a>

    <a href="{{ url('/cricket-series/league') }}"
       class="btn series-category-btn {{ ($category ?? 'all') === 'league' ? 'active' : '' }}">
        League
    </a>

</div>

    @if(!empty($seriess['seriesMapProto']))
    <div class="accordion mt-3" id="seriesAccordion">
        @foreach($seriess['seriesMapProto'] as $monthIndex => $monthItem)
        <div class="accordion-item mb-3">
            <h2 class="accordion-header" id="heading_{{ $monthIndex }}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse_{{ $monthIndex }}" aria-expanded="false"
                    aria-controls="collapse_{{ $monthIndex }}">
                    <i class="fas fa-calendar-alt me-2"></i>
                    {{ $monthItem['date'] ?? '' }}
                </button>
            </h2>
            <div id="collapse_{{ $monthIndex }}" class="accordion-collapse collapse"
                aria-labelledby="heading_{{ $monthIndex }}" data-bs-parent="#seriesAccordion">
                <div class="accordion-body p-3 bg-light">
                    @if(!empty($monthItem['series']))
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach($monthItem['series'] as $series)
                        <div class="col match-item">
                            <a href="{{ url('/cricket-series/' . $series['id'] . '/' . Str::slug($series['name'])) }}"
                                style="text-decoration: none; color: #141010;">
                                <div class='card h-100 series-card'
                                    style='box-shadow: 0 4px 6px rgba(5, 50, 89, 0.15); border: none; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s;'>
                                    <div class='card-body p-4'>
                                        <h5 class='card-title mb-2 fw-bold' style='color: #053259; font-size: 16px;'>
                                            {{ $series['name'] }}</h5>
                                        <div class='card-text small' style='color: #6c757d;'>
                                            <span>
                                                <i class="fas fa-calendar-alt me-1" style="color: #053259;"></i>
                                                {{ \Carbon\Carbon::createFromTimestampMs($series['startDt'] ?? 0)->format('d M Y') ?? '--' }}
                                                <span class="mx-1">–</span>
                                                {{ \Carbon\Carbon::createFromTimestampMs($series['endDt'] ?? 0)->format('d M Y') ?? '--' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        No series found for this month.
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info mt-4 text-center">
        <i class="fas fa-info-circle me-2"></i>
        No cricket series data found for this category. Please check back later.
    </div>
    @endif
</div>
@endsection