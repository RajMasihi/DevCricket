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
        $matches = $this->api->liveMatches();

        // Format overs with conversion for all matches
        foreach ($matches as &$match) {
            if (isset($match['matchScore']) && is_array($match['matchScore'])) {
                foreach ($match['matchScore'] as $teamKey => $teamScore) {
                    foreach (['inngs1', 'inngs2'] as $innings) {
                        if (isset($teamScore[$innings]['overs'])) {
                            $originalOver = $teamScore[$innings]['overs'];
                            $match['matchScore'][$teamKey][$innings]['overs_display'] = $this->api->formatOverDisplay($originalOver)['display'];
                            $match['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                        }
                    }
                }
            }
        }

        return response()->json([
            'matches' => $matches,
            'cached' => true,
        ]);
    }

    public function matchInfo(int|string $id): JsonResponse
    {
        $info = $this->api->matchInfo($id);
        $overThreshold = $this->calculateOverThreshold($info);
        $oversValidation = $this->getOversValidationData($id, $info);
        
        // Format overs with prediction for display
        $info = $this->formatOversWithPrediction($info);
        
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

    private function formatOversWithPrediction(array $matchData): array
    {
        if (isset($matchData['matchScore']) && is_array($matchData['matchScore'])) {
            foreach ($matchData['matchScore'] as $teamKey => $teamScore) {
                foreach (['inngs1', 'inngs2'] as $innings) {
                    if (isset($teamScore[$innings]['overs'])) {
                        $originalOver = $teamScore[$innings]['overs'];
                        $matchData['matchScore'][$teamKey][$innings]['overs_display'] = $this->api->formatOverWithPrediction($originalOver);
                        $matchData['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                    }
                }
            }
        }
        
        return $matchData;
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
                        
                        // Validate ball-by-ball progression
                        $isValid = $this->validateBallByBallProgression($currentOver, $lastOver, $teamScore[$innings]['overs']);
                        
                        $teamValidation[$innings] = [
                            'current' => $currentOver,
                            'previous' => $lastOver,
                            'is_valid' => $isValid,
                            'formatted' => $teamScore[$innings]['overs'],
                            'validation_reason' => $isValid ? 'Valid progression' : 'Invalid over progression'
                        ];
                        
                        // Update last known over only if valid
                        if ($isValid) {
                            $this->setLastKnownOver($key, $currentOver);
                        }
                    }
                }
                
                $validationData[$teamKey] = $teamValidation;
            }
        }
        
        return $validationData;
    }

    private function validateBallByBallProgression(float $currentOver, float $lastOver, string $currentOverStr): bool
    {
        // If no previous data, accept current value
        if ($lastOver === 0.0) {
            return true;
        }

        // Check for regression (decrease in over value) - this is the only strict validation
        if ($currentOver < $lastOver - 0.01) {
            return false;
        }

        // Allow any forward progression - handles missing intermediate values
        // This ensures we don't miss updates like 0.6 → 1.0 if 0.6 wasn't displayed
        if ($currentOver > $lastOver) {
            $currentOvers = (int) floor($currentOver);
            $currentBalls = round(($currentOver - $currentOvers) * 6) / 6;
            $lastOvers = (int) floor($lastOver);
            $lastBalls = round(($lastOver - $lastOvers) * 6) / 6;

            // Handle over completion scenarios
            if ($currentOvers > $lastOvers) {
                // Proper over completion (e.g., 0.6 → 1.0) or jump with missing data
                if ($currentBalls === 0.0) {
                    return true;
                }
                // Jump within same over (e.g., 0.2 → 0.5 due to missing data)
                if ($currentOvers === $lastOvers) {
                    return true;
                }
                // Jump to different over (e.g., 0.3 → 1.2 due to missing data)
                return true;
            }

            // Normal ball progression within same over
            if ($currentOvers === $lastOvers && $currentBalls > $lastBalls) {
                return true;
            }
        }

        // Same over value - no change
        if (abs($currentOver - $lastOver) < 0.01) {
            return true;
        }

        return true;
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
        $info = $this->api->matchInfo($id);
        $scorecard = $this->api->scorecard($id);
        $commentaryRaw = strtolower($info['state'] ?? '') === 'in progress'
            ? $this->api->commentary($id)
            : [];
        
        // Format overs with prediction for display
        $info = $this->formatOversWithPrediction($info);
        $scorecard = $this->formatScorecardOversWithPrediction($scorecard);
        
        return response()->json([
            'matchId' => $id,
            'info' => $info,
            'scorecard' => $scorecard,
            'commentary' => $commentaryRaw,
            'commentaryItems' => $this->api->normalizeCommentaryList($commentaryRaw),
        ]);
    }

    private function formatScorecardOversWithPrediction(array $scorecardData): array
    {
        if (isset($scorecardData['scorecard']) && is_array($scorecardData['scorecard'])) {
            foreach ($scorecardData['scorecard'] as $index => $scorecard) {
                if (isset($scorecard['overs'])) {
                    $originalOver = $scorecard['overs'];
                    $scorecardData['scorecard'][$index]['overs_display'] = $this->api->formatOverWithPrediction($originalOver);
                    $scorecardData['scorecard'][$index]['overs_original'] = $originalOver;
                }
            }
        }
        
        return $scorecardData;
    }

    public function commentary(int|string $id): JsonResponse
    {
        $commentary = $this->api->commentary($id);

        return response()->json([
            'matchId' => $id,
            'commentary' => $commentary,
            'commentaryItems' => $this->api->normalizeCommentaryList($commentary),
        ]);
    }
}
