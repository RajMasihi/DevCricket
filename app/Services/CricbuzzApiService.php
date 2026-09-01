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
        $ttl = strtolower($data['state'] ?? '') === 'in progress' ? 10 : 120;
        Cache::put($cacheKey, $data, $ttl);

        return $data;
    }

    public function scorecard(int|string $matchId): array
    {
        $info = $this->matchInfo($matchId);
        $ttl = strtolower($info['state'] ?? '') === 'in progress' ? 8 : 60;

        return $this->get("mcenter/v1/{$matchId}/scard", $ttl);
    }

    public function teams(int|string $matchId): array
    {
        return $this->get("mcenter/v1/{$matchId}/teams", 300);
    }

    public function commentary(int|string $matchId): array
    {
        $info = $this->matchInfo($matchId);
        $ttl = strtolower($info['state'] ?? '') === 'in progress' ? 8 : 60;

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
        // dd($this->parseMatchesList($this->upcomingMatchesRaw()));
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

    public function formatOverWithPrediction(string $overString): string
    {
        if (empty($overString) || $overString === '--' || $overString === '-') {
            return $overString;
        }

        $decimalOver = $this->parseOverValue($overString);
        $currentOvers = (int) floor($decimalOver);
        $currentBalls = round(($decimalOver - $currentOvers) * 6) / 6;

        // If current balls is 0, it means we just completed an over (was N.6, now N+1.0)
        // In this case, show the completed over without prediction
        if ($currentBalls === 0) {
            return (string)$currentOvers;
        }

        // Calculate the predicted completion
        $nextCompletedOver = (string)($currentOvers + 1);
        $ballsRemaining = 0.6 - $currentBalls;
        $predictedCompletion = $currentOvers . '.' . ($currentBalls + $ballsRemaining);

        // Format: nextCompletedOver(predictedCompletion)
        // Example: 2.5 → 3(2.6)
        return $nextCompletedOver . '(' . $predictedCompletion . ')';
    }

    public function formatOverDisplay(string $overString): array
    {
        if (empty($overString) || $overString === '--' || $overString === '-') {
            return [
                'display' => $overString,
                'decimal' => 0.0
            ];
        }

        $parts = explode('.', $overString);
        $overs = (float)($parts[0] ?? 0);
        $balls = isset($parts[1]) ? (float)$parts[1] : 0;

        // Handle over-end conversion: N.6 → (N+1).0
        if ($balls == 6) {
            $overs += 1;
            $balls = 0;
        }

        $decimal = $overs + ($balls / 6);

        // Display format: if balls is 0, show as whole number (e.g., "3" instead of "3.0")
        if ($balls == 0) {
            $display = (string)$overs;
        } else {
            $display = $overs . '.' . $balls;
        }

        return [
            'display' => $display,
            'decimal' => $decimal
        ];
    }

    private function parseOverValue(string $overString): float
    {
        if (empty($overString) || $overString === '--' || $overString === '-') {
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
