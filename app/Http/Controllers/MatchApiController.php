<?php

namespace App\Http\Controllers;

use App\Services\CricbuzzApiService;
use Illuminate\Http\JsonResponse;

class MatchApiController extends Controller
{
    public function __construct(private CricbuzzApiService $api) {}

    public function liveMatches(): JsonResponse
    {
        return response()->json([
            'matches' => $this->api->liveMatches(),
            'cached' => true,
        ]);
    }

    public function matchInfo(int|string $id): JsonResponse
    {
        $info = $this->api->matchInfo($id);
        $overThreshold = $this->calculateOverThreshold($info);
        
        return response()->json([
            'matchId' => $id,
            'info' => $info,
            'over_threshold' => $overThreshold,
            'websocket_config' => [
                'channel' => 'match.' . $id,
                'event' => 'MatchScoreUpdated',
                'reverb_configured' => !empty(env('REVERB_APP_KEY'))
            ]
        ]);
    }

    private function calculateOverThreshold(array $matchData): array
    {
        $maxOvers = 0.0;
        $thresholdLevel = 'normal';
        
        if (isset($matchData['matchScore']) && is_array($matchData['matchScore'])) {
            foreach ($matchData['matchScore'] as $teamScore) {
                if (isset($teamScore['inngs1']['overs'])) {
                    $overData = $this->api->formatOverDisplay($teamScore['inngs1']['overs']);
                    $maxOvers = max($maxOvers, $overData['decimal']);
                }
                if (isset($teamScore['inngs2']['overs'])) {
                    $overData = $this->api->formatOverDisplay($teamScore['inngs2']['overs']);
                    $maxOvers = max($maxOvers, $overData['decimal']);
                }
            }
        }
        
        // Thresholds based on decimal over values:
        // 6.2 overs = 6.33 decimal, 6.3 overs = 6.5 decimal, 6.4 overs = 6.67 decimal
        // Using >= for lower bounds to properly categorize exact threshold values
        if ($maxOvers >= 6.67) {
            $thresholdLevel = 'critical'; // 6.4+ overs (6.67 decimal)
        } elseif ($maxOvers >= 6.5) {
            $thresholdLevel = 'high';     // 6.3+ overs (6.5 decimal)
        } elseif ($maxOvers >= 6.33) {
            $thresholdLevel = 'medium';   // 6.2+ overs (6.33 decimal)
        }
        
        return [
            'max_overs' => $maxOvers,
            'threshold_level' => $thresholdLevel,
            'show_ball_by_ball' => $maxOvers >= 6.33,
            'real_time_update' => $maxOvers >= 6.67,
            'increased_refresh' => $maxOvers >= 6.5
        ];
    }

    public function scoreboard(int|string $id): JsonResponse
    {
        return response()->json([
            'matchId' => $id,
            'info' => $this->api->matchInfo($id),
            'scorecard' => $this->api->scorecard($id),
        ]);
    }

    public function commentary(int|string $id): JsonResponse
    {
        return response()->json([
            'matchId' => $id,
            'commentary' => $this->api->commentary($id),
        ]);
    }
}
