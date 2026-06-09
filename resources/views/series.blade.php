<?php
use Illuminate\Support\Str;
?>
@extends('layouts.main')
@section('title', 'Series')
@section('main-container')
    <div class="container-fluit main-section">
        <h3 style="text-align: center">All International/Domestic/League Series</h3>
        @if(!empty($seriess['seriesMapProto']))
            <div class="accordion mt-3" id="seriesAccordion">
                @foreach($seriess['seriesMapProto'] as $monthIndex => $monthItem)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading_{{ $monthIndex }}">
                            <!-- The button is not needed since we want it always open -->
                            <span class="accordion-button" style="background:#f8fafc;">
                                {{ $monthItem['date'] ?? '' }}
                            </span>
                        </h2>
                        <div id="collapse_{{ $monthIndex }}" class="accordion-collapse collapse show" aria-labelledby="heading_{{ $monthIndex }}" data-bs-parent="#seriesAccordion" style="display:block;">
                            <div class="accordion-body p-2">
                                @if(!empty($monthItem['series']))
                                    <div class="row row-cols-1 row-cols-md-2 g-3">
                                        @foreach($monthItem['series'] as $series)
                                            <div class="col match-item">
                                                <a href="{{ url('/serieslist'.'/'. $series['id'] .'/'. Str::slug($series['name'])) }}" style="text-decoration: none; color:#141010;">
                                                    <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                                                        <div class='card-body'>
                                                            <h5 class='card-title mb-1'>{{ $series['name'] }}</h5>
                                                            <div class='card-text small'>
                                                                <span>
                                                                    <i class="bi bi-calendar-event"></i>
                                                                    {{ \Carbon\Carbon::createFromTimestampMs($series['startDt'] ?? 0)->format('d M Y') ?? '--' }}
                                                                    &ndash;
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
                                    <span class="text-muted">No series found for this month.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mt-4">
                No cricket series data found.
            </div>
        @endif
    </div>
@endsection
