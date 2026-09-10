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
        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders($this->headers())
                ->timeout(20)
                ->get(env('CriBase_Url') . $path);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();

            return is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
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
            $cached = Cache::get($cacheKey);

            return is_array($cached) ? $cached : [];
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

        $data = $this->get("mcenter/v1/{$matchId}/hcomm", $ttl);

        if (empty($data)) {
            $data = $this->get("mcenter/v1/{$matchId}/comm", $ttl);
        }

        return $data;
    }

    public function normalizeCommentaryList(array $commentaryData): array
    {
        $raw = $commentaryData['commentaryList']
            ?? $commentaryData['commentary']
            ?? $commentaryData['commentaryLines']
            ?? [];

        if (!is_array($raw)) {
            return [];
        }

        $items = [];

        foreach ($raw as $comm) {
            if (!is_array($comm)) {
                continue;
            }

            $text = trim((string) (
                $comm['commText']
                ?? $comm['text']
                ?? $comm['commentaryText']
                ?? $comm['commentary']
                ?? ''
            ));

            $event = strtolower((string) ($comm['event'] ?? ''));
            $overSeparator = is_array($comm['overSeparator'] ?? null) ? $comm['overSeparator'] : [];

            if ($text === '' && !in_array($event, ['ball', 'wicket', 'four', 'six'], true)) {
                continue;
            }

            $over = $comm['overNumber']
                ?? $comm['over']
                ?? $overSeparator['overs']
                ?? $overSeparator['over']
                ?? '';

            $items[] = [
                'over' => (string) $over,
                'ballNbr' => $comm['ballNbr'] ?? $comm['ball'] ?? null,
                'text' => $text,
                'timestamp' => $comm['timestamp'] ?? null,
                'event' => $event ?: 'ball',
                'isWicket' => !empty($comm['isWicket']) || $event === 'wicket',
                'isFour' => !empty($comm['isFour']) || $event === 'four',
                'isSix' => !empty($comm['isSix']) || $event === 'six',
                'score' => $overSeparator['score'] ?? $comm['score'] ?? null,
                'wickets' => $overSeparator['wickets'] ?? $comm['wickets'] ?? null,
            ];
        }

        return array_slice($items, 0, 30);
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

    public function pointsTable(int|string $seriesId): array
    {
        return $this->get("stats/v1/series/{$seriesId}/points-table", 300);
    }

    public function hasPointsTable(?array $pointtable): bool
    {
        if (empty($pointtable) || !is_array($pointtable)) {
            return false;
        }

        $groups = $pointtable['pointsTable'] ?? [];
        if (!is_array($groups) || empty($groups)) {
            return false;
        }

        foreach ($groups as $group) {
            $rows = $group['pointsTableInfo'] ?? [];
            if (is_array($rows) && count($rows) > 0) {
                return true;
            }
        }

        return false;
    }

    public function enrichScorecardFromMatchInfo(array $matchInfo, array $scorecardData = []): array
    {
        if (!empty($scorecardData['scorecard']) && is_array($scorecardData['scorecard'])) {
            return $scorecardData;
        }

        $scorecardData['status'] = $scorecardData['status'] ?? ($matchInfo['status'] ?? '');
        $scorecardData['startdate'] = $scorecardData['startdate'] ?? ($matchInfo['startdate'] ?? null);
        $scorecardData['appindex']['seotitle'] = $scorecardData['appindex']['seotitle']
            ?? ($matchInfo['seriesname'] ?? ($matchInfo['seriesName'] ?? ''));

        $teamNames = [
            'team1Score' => $matchInfo['team1']['teamname'] ?? ($matchInfo['team1']['teamName'] ?? 'Team 1'),
            'team2Score' => $matchInfo['team2']['teamname'] ?? ($matchInfo['team2']['teamName'] ?? 'Team 2'),
        ];

        $cards = [];
        $matchScore = $matchInfo['matchScore'] ?? [];

        foreach ($teamNames as $scoreKey => $teamName) {
            if (!isset($matchScore[$scoreKey]) || !is_array($matchScore[$scoreKey])) {
                continue;
            }

            foreach (['inngs1', 'inngs2'] as $innings) {
                $inng = $matchScore[$scoreKey][$innings] ?? null;
                if (!is_array($inng) || empty($inng)) {
                    continue;
                }

                $runs = $inng['runs'] ?? $inng['score'] ?? null;
                $overs = $inng['overs'] ?? '';

                if ($runs === null && $overs === '') {
                    continue;
                }

                $suffix = $innings === 'inngs2' ? ' (2nd Inn)' : '';
                $cards[] = [
                    'batteamname' => $teamName . $suffix,
                    'score' => $runs ?? '',
                    'wickets' => $inng['wickets'] ?? '0',
                    'overs' => $overs,
                    'overs_display' => $this->formatOverDisplay((string) $overs)['display'],
                    'batsman' => [],
                    'bowler' => [],
                ];
            }
        }

        if (!empty($cards)) {
            $scorecardData['scorecard'] = $cards;
        }

        return $scorecardData;
    }

    public function normalizeMatchState(?array $matchInfo): string
    {
        return strtolower(trim((string) ($matchInfo['state'] ?? '')));
    }

    public function isLiveMatch(?array $matchInfo): bool
    {
        return $this->normalizeMatchState($matchInfo) === 'in progress';
    }

    public function isUpcomingMatch(?array $matchInfo): bool
    {
        $state = $this->normalizeMatchState($matchInfo);

        if ($state === '' || $this->isLiveMatch($matchInfo) || $this->isCompletedMatch($matchInfo)) {
            return false;
        }

        return in_array($state, ['upcoming', 'preview', 'match not started'], true)
            || str_contains($state, 'upcoming')
            || str_contains($state, 'not started');
    }

    public function isCompletedMatch(?array $matchInfo): bool
    {
        $state = $this->normalizeMatchState($matchInfo);

        return in_array($state, ['complete', 'completed', 'result'], true)
            || str_contains($state, 'complete');
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
        // When balls is 6, it means the over is complete
        if ($balls == 6) {
            $overs += 1;
            $balls = 0;
        }

        $decimal = $overs + ($balls / 6);

        // Display format: if balls is 0, show as whole number (e.g., "3" instead of "3.0")
        if ($balls == 0) {
            $display = (string)$overs;
        } else {
            // Display the balls as an integer (e.g., 0.5 instead of 0.833...)
            $display = $overs . '.' . (int)$balls;
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
        // When balls is 6, it means the over is complete
        if ($balls == 6) {
            $overs += 1;
            $balls = 0;
        }

        return $overs + ($balls / 6);
    }
}
