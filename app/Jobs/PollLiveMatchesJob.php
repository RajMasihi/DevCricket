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

            $broker->publish($matchId, 'score_update', [
                'info' => $info,
                'scorecard' => $scorecard,
                'commentary' => $commentary,
            ]);

            broadcast(new MatchScoreUpdated($matchId, $info, $scorecard, $commentary));
        }
    }
}
