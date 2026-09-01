<?php

namespace App\Events;

use App\Services\CricbuzzApiService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class MatchScoreUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int|string $matchId,
        public array $info,
        public array $scorecard,
        public array $commentary = [],
        public ?array $thresholdData = null,
    ) {
        // Calculate threshold data if not provided
        if ($this->thresholdData === null) {
            $this->thresholdData = $this->calculateThresholdData();
        }
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('match.' . $this->matchId),
            new Channel('live-matches'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MatchScoreUpdated';
    }

    public function broadcastWith(): array
    {
        $validationData = $this->getOversValidationData();
        
        return [
            'matchId' => $this->matchId,
            'info' => $this->info,
            'scorecard' => $this->scorecard,
            'commentary' => $this->commentary,
            'threshold' => $this->thresholdData,
            'timestamp' => now()->toISOString(),
            'overs_validation' => $validationData,
            'validation_summary' => [
                'all_valid' => $this->allOversValid($validationData),
                'invalid_count' => $this->countInvalidOvers($validationData)
            ]
        ];
    }

    private function allOversValid(array $validationData): bool
    {
        foreach ($validationData as $teamValidation) {
            foreach ($teamValidation as $inningsValidation) {
                if (!$inningsValidation['is_valid']) {
                    return false;
                }
            }
        }
        
        return true;
    }

    private function countInvalidOvers(array $validationData): int
    {
        $invalidCount = 0;
        
        foreach ($validationData as $teamValidation) {
            foreach ($teamValidation as $inningsValidation) {
                if (!$inningsValidation['is_valid']) {
                    $invalidCount++;
                }
            }
        }
        
        return $invalidCount;
    }

    private function calculateThresholdData(): array
    {
        $maxOvers = 0.0;
        $thresholdLevel = 'normal';

        // Extract overs from match data
        if (!empty($this->scorecard['scorecard'])) {
            foreach ($this->scorecard['scorecard'] as $scorecard) {
                $overs = $this->parseOverValue($scorecard['overs'] ?? '0');
                $maxOvers = max($maxOvers, $overs);
            }
        }

        if (!empty($this->info['matchScore'])) {
            foreach ($this->info['matchScore'] as $teamScore) {
                if (!empty($teamScore['inngs1']['overs'])) {
                    $overs = $this->parseOverValue($teamScore['inngs1']['overs']);
                    $maxOvers = max($maxOvers, $overs);
                }
                if (!empty($teamScore['inngs2']['overs'])) {
                    $overs = $this->parseOverValue($teamScore['inngs2']['overs']);
                    $maxOvers = max($maxOvers, $overs);
                }
            }
        }

        // Calculate threshold level based on decimal values
        if ($maxOvers >= 6.67) {
            $thresholdLevel = 'critical'; // 6.4+ overs
        } elseif ($maxOvers >= 6.5) {
            $thresholdLevel = 'high';     // 6.3+ overs
        } elseif ($maxOvers >= 6.33) {
            $thresholdLevel = 'medium';   // 6.2+ overs
        }

        return [
            'max_overs' => $maxOvers,
            'threshold_level' => $thresholdLevel,
            'show_ball_by_ball' => $maxOvers >= 6.33,
            'real_time_update' => $maxOvers >= 6.67,
            'increased_refresh' => $maxOvers >= 6.5
        ];
    }

    private function getOversValidationData(): array
    {
        $validationData = [];
        
        if (!empty($this->info['matchScore'])) {
            foreach ($this->info['matchScore'] as $teamKey => $teamScore) {
                $teamValidation = [];
                
                foreach (['inngs1', 'inngs2'] as $innings) {
                    if (!empty($teamScore[$innings]['overs'])) {
                        $key = "{$teamKey}_{$innings}";
                        $currentOver = $this->parseOverValue($teamScore[$innings]['overs']);
                        $lastOver = $this->getLastKnownOver($this->matchId, $key);
                        
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
                            $this->setLastKnownOver($this->matchId, $key, $currentOver);
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

    private function getLastKnownOver(string $matchId, string $key): float
    {
        $cacheKey = "match_overs:{$matchId}:{$key}";
        return (float) Cache::get($cacheKey, 0.0);
    }

    private function setLastKnownOver(string $matchId, string $key, float $over): void
    {
        $cacheKey = "match_overs:{$matchId}:{$key}";
        Cache::put($cacheKey, $over, now()->addHours(2)); // Cache for 2 hours
    }

    private function parseOverValue(string $overString): float
    {
        if (empty($overString) || $overString === '--') {
            return 0.0;
        }

        $parts = explode('.', $overString);
        $overs = (float)($parts[0] ?? 0);
        $balls = isset($parts[1]) ? (float)$parts[1] : 0;

        // Handle over-end conversion: N.6 → (N+1).0
        if ($balls == 6) {
            $overs += 1;
            $balls = 0;
        }

        return $overs + ($balls / 6);
    }
}
