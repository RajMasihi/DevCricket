<?php

namespace App\Jobs;

use App\Events\MatchScoreUpdated;
use App\Services\CricbuzzApiService;
use App\Services\MatchEventBroker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PollLiveMatchesJob implements ShouldQueue
{
    use Queueable;

    public function handle(CricbuzzApiService $api, MatchEventBroker $broker): void
    {
        foreach ($api->extractLiveMatchIds() as $matchId) {
            $info = $api->matchInfo($matchId);
            $scorecard = $api->scorecard($matchId);
            $commentary = [];

            if (strtolower($info['state'] ?? '') === 'in progress') {
                $commentary = $api->commentary($matchId);
            }

            // Calculate threshold data for broadcast
            $thresholdData = $this->calculateThresholdData($info, $scorecard);

            $broker->publish($matchId, 'score_update', [
                'info' => $info,
                'scorecard' => $scorecard,
                'commentary' => $commentary,
                'threshold' => $thresholdData,
            ]);

            broadcast(new MatchScoreUpdated($matchId, $info, $scorecard, $commentary, $thresholdData));
        }
    }

    private function calculateThresholdData(array $info, array $scorecard): array
    {
        $maxOvers = 0.0;
        $thresholdLevel = 'normal';

        // Extract overs from match data
        if (!empty($scorecard['scorecard'])) {
            foreach ($scorecard['scorecard'] as $sc) {
                $overs = $this->parseOverValue($sc['overs'] ?? '0');
                $maxOvers = max($maxOvers, $overs);
            }
        }

        if (!empty($info['matchScore'])) {
            foreach ($info['matchScore'] as $teamScore) {
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
