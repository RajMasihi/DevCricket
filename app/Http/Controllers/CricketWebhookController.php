<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CricketWebhookController extends Controller
{
    /**
     * Handle incoming cricket data webhook from Playwright scraper
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Validate incoming data
            $data = $request->json()->all();
            
            Log::info('Cricket webhook received', [
                'ball' => $data['ball'] ?? 'unknown',
                'batsman' => $data['batsman'] ?? 'unknown',
                'bowler' => $data['bowler'] ?? 'unknown',
                'runs' => $data['runs'] ?? 'unknown',
                'source' => $data['source'] ?? 'unknown',
                'timestamp' => $data['timestamp'] ?? now()
            ]);
            
            // Validate required fields
            $validated = $request->validate([
                'ball' => 'required|string',
                'batsman' => 'required|string',
                'bowler' => 'required|string',
                'runs' => 'required|string',
                'commentary' => 'required|string',
                'matchId' => 'nullable|string',
                'team1' => 'nullable|string',
                'team2' => 'nullable|string',
                'team1Score' => 'nullable|string',
                'team2Score' => 'nullable|string',
                'timestamp' => 'nullable|string',
                'source' => 'nullable|string'
            ]);
            
            // Store the data (you can save to database or cache)
            $this->storeCricketData($validated);
            
            // Optionally broadcast to WebSocket for real-time updates
            // event(new \App\Events\CricketScoreUpdated($validated));
            
            return response()->json([
                'success' => true,
                'message' => 'Cricket data received successfully',
                'data' => $validated
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in cricket webhook', [
                'errors' => $e->errors(),
                'data' => $request->json()->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error processing cricket webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Store cricket data to database or cache
     */
    private function storeCricketData(array $data)
    {
        try {
            // Store in cache for quick access
            $cacheKey = 'cricket_live_data_' . ($data['matchId'] ?? 'default');
            cache()->put($cacheKey, $data, now()->addMinutes(10));
            
            // Try to store in database (optional - will fail gracefully if not configured)
            try {
                DB::table('cricket_live_data')->insert([
                    'match_id' => $data['matchId'] ?? null,
                    'ball' => $data['ball'],
                    'batsman' => $data['batsman'],
                    'bowler' => $data['bowler'],
                    'runs' => $data['runs'],
                    'commentary' => $data['commentary'],
                    'team1' => $data['team1'] ?? null,
                    'team2' => $data['team2'] ?? null,
                    'team1_score' => $data['team1Score'] ?? null,
                    'team2_score' => $data['team2Score'] ?? null,
                    'source' => $data['source'] ?? 'unknown',
                    'raw_data' => json_encode($data),
                    'data_timestamp' => $data['timestamp'] ? \Carbon\Carbon::parse($data['timestamp']) : now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $dbError) {
                // Database storage is optional - continue if it fails
                Log::info('Database storage skipped (table may not exist)', [
                    'error' => $dbError->getMessage()
                ]);
            }
            
            // Store latest data globally
            cache()->put('latest_cricket_data', $data, now()->addMinutes(5));
            
            Log::info('Cricket data stored successfully', [
                'cache_key' => $cacheKey,
                'ball' => $data['ball']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error storing cricket data', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
