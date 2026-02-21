@extends('layouts.main')
<title>@yield('title', 'SeriesList')</title>
@section('main-container')
    <div class="container-fluit main-section">
        <h4 style="text-align: center; font-size:28px">Series Full Matchs : <span>{{ $series['name'] }} </span></h4>
        {{-- <h4 style="text-align: center">Series Full Matchs : <span >serttt</span></h4> --}}
        <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
            @foreach ($serieslists as $serieslist)
            <div class='col match-item'>
                <a href="{{ url('/score') }}/{{ $serieslist['id'] }}" style="text-decoration: none; color:#141010;">
                        <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                            <div class='row card-body'>
                                @php
                                    $seriesname = $serieslist['name'];
                                    $ser = explode(', ', $seriesname);
                                    $new_sername = end($ser);
                                @endphp
                                <p class='card-text col-8' style="color: #817373">@php echo $new_sername;@endphp, {{ $serieslist['venue'] }}
                                </p>
                                <p class="match-formate col-2" style='margin-top:-15px;'>
                                    @if ($serieslist['matchType'] === 'odi')
                                        <span>ODI</span>
                                    @elseif ($serieslist['matchType'] === 't20')
                                        <span class="t20-series">T20</span>
                                    @elseif ($serieslist['matchType'] === 'test')
                                        <span class="test-series">Test</span>
                                    @endif
                                </p>
                                <div class="col-6">
                                    @foreach ($serieslist['teamInfo'] as $ser)
                                        <h5 class='card-title'><img class="img" src="{{ $ser['img'] }}" alt=""
                                                srcset="" />&nbsp;&nbsp;&nbsp; {{ $ser['name'] }}</h5>
                                    @endforeach
                                </div>
                                <div class="col-6">
                                    <p class=' card-text text-align-right'>
                                    @php
                                        $dateTimeGMT = new DateTime(
                                            $serieslist['dateTimeGMT'],
                                            new DateTimeZone('GMT'),
                                        );
                                        $dateGMT = new DateTime($serieslist['dateTimeGMT']);
                                        $dateTimeGMT->setTimezone(new DateTimeZone('Asia/Kolkata'));
                                        // echo $date->format('Y-m-d h:i A');
                                        $seriesdate = strtotime($serieslist['date']);
                                        if($serieslist['matchEnded'] === false){
                                        echo date('M j', $seriesdate) .
                                            ' &nbsp;' .
                                            $dateGMT->format('h:i A') .
                                            '(GMT)/' .'<b>'.
                                            $dateTimeGMT->format('h:i A').'</b>'.
                                            '(Local)';
                                        }else{
                                            echo "Click Here Record Show !";
                                        }
                                    @endphp</p>
                                </div>
                                @if ($serieslist['matchEnded'] === true)
                                       <p class='card-text' style='color:#de280c'>{{ $serieslist['status'] }}</p>
                                @else
                                    <p class='card-text' style='color:#0c7cde'>{{ $serieslist['status'] }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
