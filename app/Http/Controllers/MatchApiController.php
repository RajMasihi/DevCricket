<?php

namespace App\Http\Controllers;

use App\Services\CricbuzzApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

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
        $oversValidation = $this->getOversValidationData($id, $info);
        
        return response()->json([
            'matchId' => $id,
            'info' => $info,
            'over_threshold' => $overThreshold,
            'overs_validation' => $oversValidation,
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

    private function getOversValidationData(string $matchId, array $matchData): array
    {
        $validationData = [];
        
        if (isset($matchData['matchScore']) && is_array($matchData['matchScore'])) {
            foreach ($matchData['matchScore'] as $teamKey => $teamScore) {
                $teamValidation = [];
                
                foreach (['inngs1', 'inngs2'] as $innings) {
                    if (isset($teamScore[$innings]['overs'])) {
                        $key = "{$matchId}_{$teamKey}_{$innings}";
                        $currentOver = $this->api->formatOverDisplay($teamScore[$innings]['overs'])['decimal'];
                        $lastOver = $this->getLastKnownOver($key);
                        
                        $teamValidation[$innings] = [
                            'current' => $currentOver,
                            'previous' => $lastOver,
                            'is_valid' => $currentOver >= $lastOver,
                            'formatted' => $teamScore[$innings]['overs']
                        ];
                        
                        // Update last known over if valid
                        if ($currentOver >= $lastOver) {
                            $this->setLastKnownOver($key, $currentOver);
                        }
                    }
                }
                
                $validationData[$teamKey] = $teamValidation;
            }
        }
        
        return $validationData;
    }

    private function getLastKnownOver(string $key): float
    {
        return (float) Cache::get("match_overs:{$key}", 0.0);
    }

    private function setLastKnownOver(string $key, float $over): void
    {
        Cache::put("match_overs:{$key}", $over, now()->addHours(2));
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
