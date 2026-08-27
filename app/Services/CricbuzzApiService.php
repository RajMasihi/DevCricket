<?php

namespace App\Services;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
// Replace $redis = new Redis(); with Facade calls:


class CricbuzzApiService
{
    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com',
            'x-rapidapi-key' => env('RAPIDAPI_KEY'),
        ];
    }

    private function fetchFromApi(string $path): array
    {
        $response = Http::withOptions(['verify' => false])
            ->withHeaders($this->headers())
            ->get(env('CriBase_Url') . $path);

        return $response->successful() ? $response->json() : [];
    }

    public function get(string $path, int $ttlSeconds = 60): array
    {
        $cacheKey = 'cricbuzz:' . md5($path);

        return Cache::remember($cacheKey, $ttlSeconds, fn () => $this->fetchFromApi($path));
    }

    public function forget(string $path): void
    {
        Cache::forget('cricbuzz:' . md5($path));
    }

    public function matchInfo(int|string $matchId): array
    {
        $path = "mcenter/v1/{$matchId}";
        $cacheKey = 'cricbuzz:' . md5($path);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $data = $this->fetchFromApi($path);
        $ttl = $this->calculateTTLBasedOnOvers($data);
        Cache::put($cacheKey, $data, $ttl);

        return $data;
    }

    private function calculateTTLBasedOnOvers(array $data): int
    {
        $state = strtolower($data['state'] ?? '');
        
        if ($state !== 'in progress') {
            return 120;
        }

        $maxOvers = $this->getMaxOversFromMatch($data);
        
        // Thresholds based on decimal over values:
        // 6.2 overs = 6.33 decimal, 6.3 overs = 6.5 decimal, 6.4 overs = 6.67 decimal
        if ($maxOvers >= 6.67) {
            return 3; // Very fast refresh for 6.4+ overs (6.67 decimal)
        } elseif ($maxOvers >= 6.5) {
            return 5; // Increased refresh rate for 6.3+ overs (6.5 decimal)
        } elseif ($maxOvers >= 6.33) {
            return 8; // Moderate refresh rate for 6.2+ overs (6.33 decimal)
        }
        
        return 10; // Default live match refresh
    }

    private function getMaxOversFromMatch(array $data): float
    {
        $maxOvers = 0.0;
        
        if (isset($data['matchScore']) && is_array($data['matchScore'])) {
            foreach ($data['matchScore'] as $teamScore) {
                if (isset($teamScore['inngs1']['overs'])) {
                    $overs = $this->parseOverValue($teamScore['inngs1']['overs']);
                    $maxOvers = max($maxOvers, $overs);
                }
                if (isset($teamScore['inngs2']['overs'])) {
                    $overs = $this->parseOverValue($teamScore['inngs2']['overs']);
                    $maxOvers = max($maxOvers, $overs);
                }
            }
        }
        
        return $maxOvers;
    }

    private function parseOverValue(string $overString): float
    {
        if (empty($overString) || $overString === '--') {
            return 0.0;
        }
        
        // Handle formats like "6.2", "6.3", "6.4", "15.1", etc.
        $parts = explode('.', $overString);
        if (count($parts) === 2) {
            $overs = (float)$parts[0];
            $balls = (float)$parts[1];
            // Convert balls to decimal (e.g., 6.2 = 6.33, 6.3 = 6.5, 6.4 = 6.67)
            return $overs + ($balls / 6);
        }
        
        return (float)$overString;
    }

    public function scorecard(int|string $matchId): array
    {
        $info = $this->matchInfo($matchId);
        $ttl = $this->calculateTTLBasedOnOvers($info);

        return $this->get("mcenter/v1/{$matchId}/scard", $ttl);
    }

    public function teams(int|string $matchId): array
    {
        return $this->get("mcenter/v1/{$matchId}/teams", 300);
    }

    public function commentary(int|string $matchId): array
    {
        $info = $this->matchInfo($matchId);
        $ttl = $this->calculateTTLBasedOnOvers($info);

        return $this->get("mcenter/v1/{$matchId}/hcomm", $ttl);
    }

    public function liveMatchesRaw(): array
    {
        return $this->get('matches/v1/live', 30);
    }

    public function recentMatchesRaw(): array
    {
        return $this->get('matches/v1/recent', 600);
    }

    public function upcomingMatchesRaw(): array
    {
        return $this->get('matches/v1/upcoming', 1200);
    }

    public function parseMatchesList(array $data): array
    {
        if (isset($data['matches']) && is_array($data['matches'])) {
            return $data['matches'];
        }

        $matches = [];

        if (!isset($data['typeMatches']) || !is_array($data['typeMatches'])) {
            return $matches;
        }

        foreach ($data['typeMatches'] as $typeMatch) {
            if (!isset($typeMatch['seriesMatches']) || !is_array($typeMatch['seriesMatches'])) {
                continue;
            }

            foreach ($typeMatch['seriesMatches'] as $seriesObj) {
                if (
                    !isset($seriesObj['seriesAdWrapper']['matches']) ||
                    !is_array($seriesObj['seriesAdWrapper']['matches'])
                ) {
                    continue;
                }

                foreach ($seriesObj['seriesAdWrapper']['matches'] as $m) {
                    $matchInfo = isset($m['matchInfo']) && is_array($m['matchInfo']) ? $m['matchInfo'] : [];
                    $matchScore = isset($m['matchScore']) && is_array($m['matchScore']) ? $m['matchScore'] : [];

                    $matches[] = [
                        'matchInfo' => $matchInfo,
                        'matchScore' => $matchScore,
                    ];
                }
            }
        }

        return $matches;
    }

    public function liveMatches(): array
    {
        return $this->parseMatchesList($this->liveMatchesRaw());
    }

    public function recentMatches(): array
    {
        return $this->parseMatchesList($this->recentMatchesRaw());
    }

    public function upcomingMatches(): array
    {
        return $this->parseMatchesList($this->upcomingMatchesRaw());
    }

    public function extractLiveMatchIds(): array
    {
        $ids = [];

        foreach ($this->liveMatches() as $match) {
            $matchId = $match['matchInfo']['matchId'] ?? null;
            if ($matchId) {
                $ids[] = $matchId;
            }
        }

        return $ids;
    }

    public function formatOverDisplay(string $overString): array
    {
        if (empty($overString) || $overString === '--') {
            return [
                'formatted' => $overString,
                'overs' => 0,
                'balls' => 0,
                'decimal' => 0.0,
                'show_ball_by_ball' => false
            ];
        }

        $parts = explode('.', $overString);
        $overs = 0;
        $balls = 0;
        $decimal = 0.0;

        if (count($parts) === 2) {
            $overs = (int)$parts[0];
            $balls = (int)$parts[1];
            $decimal = $overs + ($balls / 6);
        } else {
            $overs = (int)$overString;
            $decimal = (float)$overString;
        }

        // Determine if ball-by-ball display should be shown (above 6.2 = >= 6.33 decimal)
        $showBallByBall = $decimal >= 6.33;

        return [
            'formatted' => $overString,
            'overs' => $overs,
            'balls' => $balls,
            'decimal' => $decimal,
            'show_ball_by_ball' => $showBallByBall,
            'breakdown' => $showBallByBall ? $this->getOverBreakdown($overs, $balls) : null
        ];
    }

    private function getOverBreakdown(int $overs, int $balls): array
    {
        $breakdown = [];
        
        for ($i = 1; $i <= $overs; $i++) {
            $breakdown[] = [
                'over' => $i,
                'complete' => true,
                'balls' => 6
            ];
        }
        
        if ($balls > 0) {
            $breakdown[] = [
                'over' => $overs + 1,
                'complete' => false,
                'balls' => $balls
            ];
        }
        
        return $breakdown;
    }
}
