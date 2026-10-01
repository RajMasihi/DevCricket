<?php

use App\Http\Controllers\MatchApiController;
use App\Http\Controllers\CricketWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/live-matches', [MatchApiController::class, 'liveMatches']);
Route::get('/match/{id}/info', [MatchApiController::class, 'matchInfo']);
Route::get('/match/{id}/scoreboard', [MatchApiController::class, 'scoreboard']);
Route::get('/match/{id}/commentary', [MatchApiController::class, 'commentary']);

// Webhook endpoint for Playwright scraper
Route::post('/v1/cricket-webhook', [CricketWebhookController::class, 'handleWebhook']);
