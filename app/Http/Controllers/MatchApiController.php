<?php

namespace App\Http\Controllers;

use App\Services\CricbuzzApiService;
use Illuminate\Http\JsonResponse;

class MatchApiController extends Controller
{
    public function __construct(private CricbuzzApiService $api) {}

    public function liveMatches(): JsonResponse
    {
        return response()->json([
            'matches' => $this->api->liveMatches(),
            'cached' => true,
        ]);
    }

    public function matchInfo(int|string $id): JsonResponse
    {
        return response()->json([
            'matchId' => $id,
            'info' => $this->api->matchInfo($id),
        ]);
    }

    public function scoreboard(int|string $id): JsonResponse
    {
        return response()->json([
            'matchId' => $id,
            'info' => $this->api->matchInfo($id),
            'scorecard' => $this->api->scorecard($id),
        ]);
    }

    public function commentary(int|string $id): JsonResponse
    {
        return response()->json([
            'matchId' => $id,
            'commentary' => $this->api->commentary($id),
        ]);
    }
}
