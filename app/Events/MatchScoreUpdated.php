<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchScoreUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int|string $matchId,
        public array $info,
        public array $scorecard,
        public array $commentary = [],
    ) {}

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
        ];
    }
}
