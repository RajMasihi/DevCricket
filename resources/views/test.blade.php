@extends('layouts.main')
@section('main-container')
<div class="container" style="margin-top: 18px;">
    <h2>Live Cricket Matches</h2>
    @if($error)
        <span style="color:red;">{{ $error }}</span>
    @elseif(count($matches) === 0)
        <p>No Recent matches found.</p>
    @else
        @foreach($matches as $match)
            <div style="border:1px solid #ccc; margin:10px 0; padding:10px; border-radius:6px;">
                <strong> {{ $match['matchDesc'] ?? 'Live Match' }}.{{ $match['matchTitle'] ?? 'Live Match' }}</strong><br>
                <span>
                    @if(isset($match['team1']) && isset($match['team2']))
                        {{ $match['team1']['teamName'] ?? 'Team 1' }} vs {{ $match['team2']['teamName'] ?? 'Team 2' }}
                    @else
                        Teams: N/A
                    @endif
                </span><br>
                <small>Status: {{ $match['status'] ?? 'Status not available' }}</small>
            </div>
        @endforeach
    @endif
</div>
@endsection