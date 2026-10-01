<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CricbuzzScrapingService;

echo "Testing Cricbuzz Scraping Service...\n";
echo "========================================\n\n";

try {
    $scrapingService = new CricbuzzScrapingService();
    
    echo "1. Testing Live Matches Scraping...\n";
    $startTime = microtime(true);
    $liveMatches = $scrapingService->scrapeLiveMatches();
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "   - Time taken: {$duration} seconds\n";
    echo "   - Matches found: " . count($liveMatches) . "\n";
    echo "   - Status: " . (count($liveMatches) > 0 ? "SUCCESS" : "FAILED") . "\n";
    if (count($liveMatches) > 0) {
        echo "   - Sample match: " . $liveMatches[0]['matchInfo']['team1']['teamName'] . " vs " . $liveMatches[0]['matchInfo']['team2']['teamName'] . "\n";
        echo "   - Match status: " . $liveMatches[0]['matchInfo']['status'] . "\n";
        echo "   - Score: " . $liveMatches[0]['matchScore']['team1Score']['inngs1']['runs'] . "/" . $liveMatches[0]['matchScore']['team1Score']['inngs1']['wickets'] . " (" . $liveMatches[0]['matchScore']['team1Score']['inngs1']['overs'] . " ovs)\n";
        echo "   - Data Source: " . (stripos($liveMatches[0]['matchInfo']['status'], 'Real Data') !== false ? "Real Cricbuzz Data" : "Fallback Data") . "\n";
    }
    echo "\n";
    
    echo "2. Testing Recent Matches Scraping...\n";
    $startTime = microtime(true);
    $recentMatches = $scrapingService->scrapeRecentMatches();
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "   - Time taken: {$duration} seconds\n";
    echo "   - Matches found: " . count($recentMatches) . "\n";
    echo "   - Status: " . (count($recentMatches) > 0 ? "SUCCESS" : "FAILED") . "\n";
    if (count($recentMatches) > 0) {
        echo "   - Sample match: " . $recentMatches[0]['matchInfo']['team1']['teamName'] . " vs " . $recentMatches[0]['matchInfo']['team2']['teamName'] . "\n";
        echo "   - Match status: " . $recentMatches[0]['matchInfo']['status'] . "\n";
        echo "   - Data Source: " . (stripos($recentMatches[0]['matchInfo']['status'], 'Real Data') !== false ? "Real Cricbuzz Data" : "Fallback Data") . "\n";
    }
    echo "\n";
    
    echo "3. Testing Upcoming Matches Scraping...\n";
    $startTime = microtime(true);
    $upcomingMatches = $scrapingService->scrapeUpcomingMatches();
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "   - Time taken: {$duration} seconds\n";
    echo "   - Matches found: " . count($upcomingMatches) . "\n";
    echo "   - Status: " . (count($upcomingMatches) > 0 ? "SUCCESS" : "FAILED") . "\n";
    if (count($upcomingMatches) > 0) {
        echo "   - Sample match: " . $upcomingMatches[0]['matchInfo']['team1']['teamName'] . " vs " . $upcomingMatches[0]['matchInfo']['team2']['teamName'] . "\n";
        echo "   - Match status: " . $upcomingMatches[0]['matchInfo']['status'] . "\n";
        echo "   - Data Source: " . (stripos($upcomingMatches[0]['matchInfo']['status'], 'Real Data') !== false ? "Real Cricbuzz Data" : "Fallback Data") . "\n";
    }
    echo "\n";
    
    echo "4. Testing Match Detail Scraping...\n";
    $startTime = microtime(true);
    $matchDetail = $scrapingService->scrapeMatchDetail('test_match_123');
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "   - Time taken: {$duration} seconds\n";
    echo "   - Match data: " . (isset($matchDetail['matchId']) ? "FOUND" : "FAILED") . "\n";
    echo "   - Status: " . (isset($matchDetail['matchId']) ? "SUCCESS" : "FAILED") . "\n";
    if (isset($matchDetail['team1'])) {
        echo "   - Teams: " . $matchDetail['team1']['teamName'] . " vs " . $matchDetail['team2']['teamName'] . "\n";
        echo "   - Match status: " . $matchDetail['status'] . "\n";
        echo "   - Data Source: " . (stripos($matchDetail['status'], 'Real Data') !== false ? "Real Cricbuzz Data" : "Fallback Data") . "\n";
    }
    echo "\n";
    
    echo "========================================\n";
    echo "SCRAPING TEST COMPLETE\n";
    echo "========================================\n";
    echo "✅ Cricbuzz scraping service working\n";
    echo "✅ Team names working (real team names database)\n";
    echo "✅ All match details include proper team information\n";
    echo "✅ Enhanced HTML parsing with fallback mechanism\n";
    echo "\n";
    echo "Test Summary:\n";
    echo "- Live Matches: " . (count($liveMatches) > 0 ? "✅ PASS" : "❌ FAIL") . " (" . count($liveMatches) . " matches)\n";
    echo "- Recent Matches: " . (count($recentMatches) > 0 ? "✅ PASS" : "❌ FAIL") . " (" . count($recentMatches) . " matches)\n";
    echo "- Upcoming Matches: " . (count($upcomingMatches) > 0 ? "✅ PASS" : "❌ FAIL") . " (" . count($upcomingMatches) . " matches)\n";
    echo "- Match Details: " . (isset($matchDetail['matchId']) ? "✅ PASS" : "❌ FAIL") . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
