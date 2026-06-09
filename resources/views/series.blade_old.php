@extends('layouts.main')
<title>@yield('title', 'Series')</title>
@section('main-container')
    <div class="container-fluit main-section">
        <h3 style="text-align: center"> AllSeries</h3>
        <div class="row row-cols-1 row-cols-md-4 g-4 pt-3">

            @foreach ($seriess as $series)
            <div class='col match-item'>
                <a href="{{ url('/serieslist') }}/{{ $series['id'] }}" style="text-decoration: none; color:#141010;">
                        <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                            <div class="match-formate">
                                @if ($series['odi'] !== 0)
                                    <span>Odi</span>
                                    @if ($series['odi'] !== 0 && $series['t20'] !== 0)
                                        <span class="t20-series">T20</span>
                                    @endif
                                    @if ($series['odi'] !== 0 && $series['t20'] !== 0 && $series['test'] !== 0)
                                        <span class="test-series">Test</span>
                                    @endif
                                @elseif($series['t20'] !== 0)
                                    <span class="t20-series">T20</span>
                                @elseif($series['test'] !== 0)
                                    <span class="test-series">Test</span>
                                @endif
                            </div>
                            <div class='card-body'>
                                <h5 class='card-title'>{{ $series['name'] }}</h5>
                                <p class='card-text'>@php
                                    $de = strtotime($series['startDate']);
                                    echo date('M j', $de);
                                    //  echo $dat = $date->format("F j, Y");
                                echo '-'.$series['endDate'].' '.date('Y',$de); @endphp </p>
                                <p class='card-text'>{{ $series['matches'] }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
