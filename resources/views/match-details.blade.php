<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<h2>{{ $matchDetails['event_home_team'] }} vs {{ $matchDetails['event_away_team'] }}</h2>

<p>Score : {{ $matchDetails['event_home_final_result'] }}</p>
<p>Venue : {{ $matchDetails['event_stadium'] }}</p>
<p>Toss : {{ $matchDetails['event_toss'] }}</p>
<p>Status : {{ $matchDetails['event_status_info'] }}</p>
@if(isset($matchDetails['scorecard']))

{{-- @if(isset($matchDetails['comments']['Live']))

<h3>Live Commentary</h3>

 @foreach($matchDetails['comments']['Live'] as $comment)

<p>
Over {{ $comment['overs'] }} :
{{ $comment['post'] }} → Runs : {{ $comment['runs'] }}
</p>

@endforeach

@endif --}}


<div id="scorecard-container">
    @foreach($matchDetails['scorecard'] as $inning => $players)

    <h3>{{ $inning }}</h3>

    <table border="1" cellpadding="8">
    <tr>
        <th>Player</th>
        <th>Runs</th>
        <th>Balls</th>
        <th>4s</th>
        <th>6s</th>
        <th>Status</th>
    </tr>

    @foreach($players as $player)
    <tr>
        <td>{{ $player['player'] }}</td>
        <td>{{ $player['R'] }}</td>
        <td>{{ $player['B'] }}</td>
        <td>{{ $player['4s'] }}</td>
        <td>{{ $player['6s'] }}</td>
        <td>{{ $player['status'] }}</td>
    </tr>
    @endforeach

    </table>
    @endforeach
</div>

@endif
<script>
setInterval(function () {
    $("#scorecard-container").load(location.href + " #scorecard-container>*", "");
}, 6000);
</script>