<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

/**
 * Publishes match update events to Redis (Kafka-compatible event bus pattern).
 * Downstream consumers: PollLiveMatchesJob listeners, WebSocket broadcasters.
 */
class MatchEventBroker
{
    public const CHANNEL = 'match-updates';

    public function publish(int|string $matchId, string $type, array $payload): void
    {
        $message = json_encode([
            'matchId' => $matchId,
            'type' => $type,
            'payload' => $payload,
            'timestamp' => now()->toIso8601String(),
        ]);

        try {
            Redis::publish(self::CHANNEL, $message);
        } catch (\Throwable) {
            // Redis unavailable — broadcasting event still handles WebSocket path
        }
    }
}
