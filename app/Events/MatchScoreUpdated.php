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
        return [
            'matchId' => $this->matchId,
            'info' => $this->info,
            'scorecard' => $this->scorecard,
            'commentary' => $this->commentary,
            'threshold' => $this->thresholdData,
            'timestamp' => now()->toISOString(),
            'overs_validation' => $this->getOversValidationData(),
        ];
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
                        
                        $teamValidation[$innings] = [
                            'current' => $currentOver,
                            'previous' => $lastOver,
                            'is_valid' => $currentOver >= $lastOver,
                            'formatted' => $teamScore[$innings]['overs']
                        ];
                        
                        // Update last known over if valid
                        if ($currentOver >= $lastOver) {
                            $this->setLastKnownOver($this->matchId, $key, $currentOver);
                        }
                    }
                }
                
                $validationData[$teamKey] = $teamValidation;
            }
        }
        
        return $validationData;
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
        if (count($parts) === 2) {
            $overs = (float)$parts[0];
            $balls = (float)$parts[1];
            return $overs + ($balls / 6);
        }

        return (float)$overString;
    }
}
