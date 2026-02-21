@foreach($matches as $match)

<div class="card mb-3 p-3">
    <h4>
        {{ $match['event_home_team'] }} vs {{ $match['event_away_team'] }}
    </h4>

    <p>Date : {{ $match['event_date_start'] }}</p>
    <p>Status : {{ $match['event_status'] }}</p>
    <p>League : {{ $match['league_name'] }}</p>

    <a href="{{ route('match.details', $match['event_key']) }}" class="btn btn-primary">
        Match Summary
    </a>
</div>

@endforeach
