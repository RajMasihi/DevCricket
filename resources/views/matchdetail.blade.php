@extends('layouts.main')
<title>@yield('title', 'Result Matchs')</title>
@section('main-container')
    <div class="container-fluit main-section">
        <h3 class="text-center">Cricket Live ScoreBoard</h3>
        <div class="row row-cols-1 row-cols-md-2 g-4 pt-3">
            @if (!empty($matches['name']))
                <div class='col match-item scoreboard'>
                    <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                        <div class='row card-body'>
                            <div class='col match-item scoreboard' id="scoreboard">
                                <div class='row card-body'>
                                    <?php
                                        $inning1 = $matches['score'][0]['inning'];
                                        $result1 = explode(" Inning", $inning1)[0];  
                                        $inning2 = $matches['score'][1]['inning'];
                                        $result2 = trim(explode(" Inning", $inning2)[0]);
                                    ?>
                                    @if ($result1)
                                        <h3>{{ $result1 }}</h3>
                                        <h4>Batting</h4>
                                        <table border="1" cellpadding="3">
                                            <tr>
                                                <th>Batsman Name</th>
                                                <th>R</th>
                                                <th>B</th>
                                                <th>4s</th>
                                                <th>6s</th>
                                                <th>SR</th>
                                            </tr>
                                            @foreach ($matches['scorecard'][0]['batting'] as $bat)
                                                <tr>
                                                    <td style="font-size:16px">{{ $bat['batsman']['name'] }}</br><span style="color:#8f3838; font-size:13px">{{ $bat['dismissal-text']}}</span></td>
                                                    <td>{{ $bat['r'] }}</td>
                                                    <td>{{ $bat['b'] }}</td>
                                                    <td>{{ $bat['4s'] }}</td>
                                                    <td>{{ $bat['6s'] }}</td>
                                                    <td>{{ $bat['sr'] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>

                                        <h4>Bowling</h4>
                                        <table border="1" cellpadding="3">
                                            <tr>
                                                <th>Bowler</th>
                                                <th>O</th>
                                                <th>R</th>
                                                <th>W</th>
                                                <th>ECO</th>
                                            </tr>
                                            @foreach ($matches['scorecard'][0]['bowling'] as $bowl)
                                                <tr>
                                                    <td>{{ $bowl['bowler']['name'] }}</td>
                                                    <td>{{ $bowl['o'] }}</td>
                                                    <td>{{ $bowl['r'] }}</td>
                                                    <td>{{ $bowl['w'] }}</td>
                                                    <td>{{ $bowl['eco'] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    @endif
                                </div>
                                <div class='col row card-body match-item' style="display: none" id='scorecard2'>
                                    @if ($matches['score'][0]['inning'])
                                        <h3>{{ $matches['score'][0]['inning'] }}</h3>
                                        <h4>Batting</h4>
                                        <table border="1" cellpadding="3">
                                            <tr>
                                                <th>Batsman</th>
                                                <th>R</th>
                                                <th>B</th>
                                                <th>4s</th>
                                                <th>6s</th>
                                                <th>SR</th>
                                            </tr>
                                            @foreach ($matches['scorecard'][0]['batting'] as $bat)
                                                <tr>
                                                    <td>{{ $bat['batsman']['name'] }}</td>
                                                    <td>{{ $bat['r'] }}</td>
                                                    <td>{{ $bat['b'] }}</td>
                                                    <td>{{ $bat['4s'] }}</td>
                                                    <td>{{ $bat['6s'] }}</td>
                                                    <td>{{ $bat['sr'] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>

                                        <h4>Bowling</h4>
                                        <table border="1" cellpadding="3">
                                            <tr>
                                                <th>Bowler</th>
                                                <th>O</th>
                                                <th>R</th>
                                                <th>W</th>
                                                <th>ECO</th>
                                            </tr>
                                            @foreach ($matches['scorecard'][0]['bowling'] as $bowl)
                                                <tr>
                                                    <td>{{ $bowl['bowler']['name'] }}</td>
                                                    <td>{{ $bowl['o'] }}</td>
                                                    <td>{{ $bowl['r'] }}</td>
                                                    <td>{{ $bowl['w'] }}</td>
                                                    <td>{{ $bowl['eco'] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    @endif
                                </div>
                            </div>

                        </div>


                    </div>
                </div>
                <div class='col match-item scoreboard'>
                    <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                        <div class='col match-item scoreboard' id="scoreboard">
                            <div class='row card-body'>
                                @if ($result2)
                                    <h3>{{ $result2 }}</h3>
                                    <h4>Batting</h4>
                                    <table border="1" cellpadding="3">
                                        <tr>
                                            <th>Batsman Name</th>
                                            <th>R</th>
                                            <th>B</th>
                                            <th>4s</th>
                                            <th>6s</th>
                                            <th>SR</th>
                                        </tr>
                                        @foreach ($matches['scorecard'][1]['batting'] as $bat)
                                            <tr>
                                                <td style="font-size:16px">{{ $bat['batsman']['name'] }}</br><span style="color:#8f3838; font-size:13px">{{ $bat['dismissal-text']}}</span></td>
                                                <td>{{ $bat['r'] }}</td>
                                                <td>{{ $bat['b'] }}</td>
                                                <td>{{ $bat['4s'] }}</td>
                                                <td>{{ $bat['6s'] }}</td>
                                                <td>{{ $bat['sr'] }}</td>
                                            </tr>
                                        @endforeach
                                    </table>

                                    <h4>Bowling</h4>
                                    <table border="1" cellpadding="3">
                                        <tr>
                                            <th>Bowler</th>
                                            <th>O</th>
                                            <th>R</th>
                                            <th>W</th>
                                            <th>ECO</th>
                                        </tr>
                                        @foreach ($matches['scorecard'][1]['bowling'] as $bowl)
                                            <tr>
                                                <td>{{ $bowl['bowler']['name'] }}</td>
                                                <td>{{ $bowl['o'] }}</td>
                                                <td>{{ $bowl['r'] }}</td>
                                                <td>{{ $bowl['w'] }}</td>
                                                <td>{{ $bowl['eco'] }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </div>
                            <div class='col row card-body match-item' style="display: none" id='scorecard2'>
                                @if ($matches['score'][1]['inning'])
                                    <h3>{{ $matches['score'][1]['inning'] }}</h3>
                                    <h4>Batting</h4>
                                    <table border="1" cellpadding="3">
                                        <tr>
                                            <th>Batsman</th>
                                            <th>R</th>
                                            <th>B</th>
                                            <th>4s</th>
                                            <th>6s</th>
                                            <th>SR</th>
                                        </tr>
                                        @foreach ($matches['scorecard'][1]['batting'] as $bat)
                                            <tr>
                                                <td>{{ $bat['batsman']['name'] }}</td>
                                                <td>{{ $bat['r'] }}</td>
                                                <td>{{ $bat['b'] }}</td>
                                                <td>{{ $bat['4s'] }}</td>
                                                <td>{{ $bat['6s'] }}</td>
                                                <td>{{ $bat['sr'] }}</td>
                                            </tr>
                                        @endforeach
                                    </table>

                                    <h4>Bowling</h4>
                                    <table border="1" cellpadding="3">
                                        <tr>
                                            <th>Bowler</th>
                                            <th>O</th>
                                            <th>R</th>
                                            <th>W</th>
                                            <th>ECO</th>
                                        </tr>
                                        @foreach ($matches['scorecard'][1]['bowling'] as $bowl)
                                            <tr>
                                                <td>{{ $bowl['bowler']['name'] }}</td>
                                                <td>{{ $bowl['o'] }}</td>
                                                <td>{{ $bowl['r'] }}</td>
                                                <td>{{ $bowl['w'] }}</td>
                                                <td>{{ $bowl['eco'] }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
            <div class='col match-item scoreboard'>
                <div class='card h-100' style='box-shadow: 2px 2px 6px 1px #053259;'>
                    <h3 class="text-center">Match Not Started !!</h3>
                </div>
            </div>
            @endif
        </div>

    </div>
@endsection
