<?php

namespace App\Http\Controllers;

use App\Services\CricbuzzApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Cricketlivescorecontroller extends Controller
{
    public function __construct(private CricbuzzApiService $cricbuzzApi) {}

    // Static Pages
    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function gallery()
    {
        return view('gallery');
    }

    public function sitemap()
    {
        return response()->view('sitemap')->header('Content-Type', 'application/xml');
    }

    // serires match function start
    public function series()
    {
        $apiUrl = env('CriBase_Url') . "series/v1/all";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Accept' => 'application/json',
                'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'x-rapidapi-key' => env('RAPIDAPI_KEY'),
            ])->get($apiUrl);

            $seriess = $response->json();
        } else {
            // Pass error msg to view or fallback mode
            $seriess = [];
            $errorMsg = $e->getMessage();
            return view('series', compact('seriess', 'errorMsg'));
        }
            // echo "<pre>";print_r($seriess);die;
        return view('series', compact('seriess'));
    }

   
    public function serieslist($id)
    {
        $apiUrl = env('CriBase_Url') . "series/v1/{$id}";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Accept' => 'application/json',
                'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'x-rapidapi-key' => env('RAPIDAPI_KEY'),
            ])->get($apiUrl);

            $serieslists = $response->json();
            $hasPointTable = $this->cricbuzzApi->hasPointsTable($this->cricbuzzApi->pointsTable($id));
        } else {
            // Pass error msg to view or fallback mode
            $serieslists = [];
            $hasPointTable = false;
            $errorMsg = $e->getMessage();
            return view('serieslist', compact('serieslists', 'hasPointTable', 'errorMsg'));
        }
            // echo "<pre>";print_r($serieslists);die;
        return view('serieslist', compact('serieslists', 'hasPointTable'));
    }
    // serires match function end

    // Live match function start

    public function result()
    {
        $matches = $this->cricbuzzApi->recentMatches();

        // Format overs with conversion for all matches
        foreach ($matches as &$match) {
            if (isset($match['matchScore']) && is_array($match['matchScore'])) {
                foreach ($match['matchScore'] as $teamKey => $teamScore) {
                    foreach (['inngs1', 'inngs2'] as $innings) {
                        if (isset($teamScore[$innings]['overs'])) {
                            $originalOver = $teamScore[$innings]['overs'];
                            $match['matchScore'][$teamKey][$innings]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                            $match['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                        }
                    }
                }
            }
        }

        return view('result', [
            'matches' => $matches,
            'error' => null,
        ]);
    }

    public function CricketliveScores()
    {
        $liveMatches = $this->cricbuzzApi->liveMatches();
        $recentMatches = $this->cricbuzzApi->recentMatches();
        $upcomingMatches = $this->cricbuzzApi->upcomingMatches();
        
        // Fetch news data
        $headers = [
            'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
            'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
            'Content-Type'    => 'application/json',
        ];
        
        $newsItems = [];
        $categories = [];
        
        try {
            // Get news categories
            $catResponse = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)->get(env('CriBase_Url') . "news/v1/cat");
            $newscat = $catResponse->json();
            
            $categories = isset($newscat['storyType']) && is_array($newscat['storyType'])
                ? $newscat['storyType']
                : [];
            
            // Get news from first category (or default category)
            if (!empty($categories) && isset($categories[0]) && isset($categories[0]['id'])) {
                $activeCategoryId = $categories[0]['id'];
                if (!empty($activeCategoryId)) {
                    $listResponse = Http::withOptions([
                        'verify' => false,
                    ])->withHeaders($headers)->get(env('CriBase_Url') . "news/v1/cat/{$activeCategoryId}");
                    $newsData = $listResponse->json();
                    $newsItems = isset($newsData['storyList']) && is_array($newsData['storyList'])
                        ? array_slice($newsData['storyList'], 0, 5) // Get first 5 news items
                        : [];
                }
            }
        } catch (\Exception $e) {
            // Handle error silently, news will just be empty
            $categories = [];
            $newsItems = [];
        }
        
        // Format overs with conversion for live matches
        foreach ($liveMatches as &$match) {
            if (isset($match['matchScore']) && is_array($match['matchScore'])) {
                foreach ($match['matchScore'] as $teamKey => $teamScore) {
                    foreach (['inngs1', 'inngs2'] as $innings) {
                        if (isset($teamScore[$innings]['overs'])) {
                            $originalOver = $teamScore[$innings]['overs'];
                            $match['matchScore'][$teamKey][$innings]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                            $match['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                        }
                    }
                }
            }
        }
        
        // Format overs with conversion for recent matches
        if (is_array($recentMatches)) {
            foreach ($recentMatches as &$match) {
                if (isset($match['matchScore']) && is_array($match['matchScore'])) {
                    foreach ($match['matchScore'] as $teamKey => $teamScore) {
                        foreach (['inngs1', 'inngs2'] as $innings) {
                            if (isset($teamScore[$innings]['overs'])) {
                                $originalOver = $teamScore[$innings]['overs'];
                                $match['matchScore'][$teamKey][$innings]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                                $match['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                            }
                        }
                    }
                }
            }
        }
        
        // Format overs with conversion for upcoming matches
        if (is_array($upcomingMatches)) {
            foreach ($upcomingMatches as &$match) {
                if (isset($match['matchScore']) && is_array($match['matchScore'])) {
                    foreach ($match['matchScore'] as $teamKey => $teamScore) {
                        foreach (['inngs1', 'inngs2'] as $innings) {
                            if (isset($teamScore[$innings]['overs'])) {
                                $originalOver = $teamScore[$innings]['overs'];
                                $match['matchScore'][$teamKey][$innings]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                                $match['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                            }
                        }
                    }
                }
            }
        }
        
        // Determine which matches to display
        $matchesToDisplay = [];
        $hasLiveMatches = is_array($liveMatches) && count($liveMatches) > 0;
        
        if ($hasLiveMatches) {
            // Display live matches and some recent matches
            $recentToDisplay = is_array($recentMatches) ? array_slice($recentMatches, 0, 3) : [];
            $matchesToDisplay = array_merge($liveMatches, $recentToDisplay);
        } else {
            // Display 3 recent matches when no live matches
            $matchesToDisplay = is_array($recentMatches) ? array_slice($recentMatches, 0, 4) : [];
        }
        
        return view('index', [
            'matches' => $matchesToDisplay,
            'liveMatches' => $liveMatches,
            'recentMatches' => $recentMatches,
            'upcomingMatches' => $upcomingMatches,
            'hasLiveMatches' => $hasLiveMatches,
            'newsItems' => $newsItems,
            'categories' => $categories,
            'error' => null,
        ]);

    }

    public function upcoming()
    {
        $matches = $this->cricbuzzApi->upcomingMatches();
        $liveMatches = $this->cricbuzzApi->liveMatches();
        $recentMatches = $this->cricbuzzApi->recentMatches();

        // Format overs with conversion for all matches
        if (is_array($matches)) {
            foreach ($matches as &$match) {
                if (isset($match['matchScore']) && is_array($match['matchScore'])) {
                    foreach ($match['matchScore'] as $teamKey => $teamScore) {
                        foreach (['inngs1', 'inngs2'] as $innings) {
                            if (isset($teamScore[$innings]['overs'])) {
                                $originalOver = $teamScore[$innings]['overs'];
                                $match['matchScore'][$teamKey][$innings]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                                $match['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                            }
                        }
                    }
                }
            }
        }

        // Format overs for live matches
        if (is_array($liveMatches)) {
            foreach ($liveMatches as &$match) {
                if (isset($match['matchScore']) && is_array($match['matchScore'])) {
                    foreach ($match['matchScore'] as $teamKey => $teamScore) {
                        foreach (['inngs1', 'inngs2'] as $innings) {
                            if (isset($teamScore[$innings]['overs'])) {
                                $originalOver = $teamScore[$innings]['overs'];
                                $match['matchScore'][$teamKey][$innings]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                                $match['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                            }
                        }
                    }
                }
            }
        }

        // Format overs for recent matches
        if (is_array($recentMatches)) {
            foreach ($recentMatches as &$match) {
                if (isset($match['matchScore']) && is_array($match['matchScore'])) {
                    foreach ($match['matchScore'] as $teamKey => $teamScore) {
                        foreach (['inngs1', 'inngs2'] as $innings) {
                            if (isset($teamScore[$innings]['overs'])) {
                                $originalOver = $teamScore[$innings]['overs'];
                                $match['matchScore'][$teamKey][$innings]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                                $match['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                            }
                        }
                    }
                }
            }
        }

        // Fetch news data
        $headers = [
            'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
            'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
            'Content-Type'    => 'application/json',
        ];
        
        $newsItems = [];
        $categories = [];
        
        try {
            // Get news categories
            $catResponse = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)->get(env('CriBase_Url') . "news/v1/cat");
            $newscat = $catResponse->json();
            
            $categories = isset($newscat['storyType']) && is_array($newscat['storyType'])
                ? $newscat['storyType']
                : [];
            
            // Get news from first category (or default category)
            if (!empty($categories) && isset($categories[0]) && isset($categories[0]['id'])) {
                $activeCategoryId = $categories[0]['id'];
                if (!empty($activeCategoryId)) {
                    $listResponse = Http::withOptions([
                        'verify' => false,
                    ])->withHeaders($headers)->get(env('CriBase_Url') . "news/v1/cat/{$activeCategoryId}");
                    $newsData = $listResponse->json();
                    $newsItems = isset($newsData['storyList']) && is_array($newsData['storyList'])
                        ? array_slice($newsData['storyList'], 0, 5) // Get first 5 news items
                        : [];
                }
            }
        } catch (\Exception $e) {
            // Handle error silently, news will just be empty
            $categories = [];
            $newsItems = [];
        }

        // For upcoming page, prioritize upcoming matches but show some live/recent if available
        $matchesToDisplay = [];
        $hasLiveMatches = is_array($liveMatches) && count($liveMatches) > 0;
        
        // Always show upcoming matches first on the upcoming page
        if (is_array($matches) && count($matches) > 0) {
            $matchesToDisplay = $matches;
        }
        
        // If no upcoming matches, show live matches
        if (count($matchesToDisplay) === 0 && $hasLiveMatches) {
            $matchesToDisplay = $liveMatches;
        }
        
        // If still no matches, show recent matches
        if (count($matchesToDisplay) === 0 && is_array($recentMatches) && count($recentMatches) > 0) {
            $matchesToDisplay = array_slice($recentMatches, 0, 4);
        }
    
        return view('index', [
            'matches' => $matchesToDisplay,
            'liveMatches' => $liveMatches,
            'recentMatches' => $recentMatches,
            'upcomingMatches' => $matches,
            'hasLiveMatches' => $hasLiveMatches,
            'newsItems' => $newsItems,
            'categories' => $categories,
            'error' => null,
        ]);
    }
    // Live match function end

    public function matchDetail(Request $request, $id, $name = null)
    {
        $tab = $request->query('tab', 'informe');
        if (!in_array($tab, ['informe', 'scoreboard', 'players'], true)) {
            $tab = 'informe';
        }

        try {
            $scorecardDatainfo = $this->cricbuzzApi->matchInfo($id);

            if (empty($scorecardDatainfo)) {
                return view('matchdetail', [
                    'scorecardDatainfo' => [],
                    'scorecardData' => [],
                    'teamsData' => [],
                    'commentary' => [],
                    'commentaryItems' => [],
                    'hasPointTable' => false,
                    'tab' => $tab,
                    'errorMsg' => 'Match details are not available for this match.',
                ]);
            }

            $scorecardData = $this->cricbuzzApi->scorecard($id);
            $scorecardData = $this->cricbuzzApi->enrichScorecardFromMatchInfo($scorecardDatainfo, $scorecardData);

            $teamsData = $this->cricbuzzApi->teams($id);

            $matchState = $this->cricbuzzApi->normalizeMatchState($scorecardDatainfo);

            $commentary = $this->cricbuzzApi->isLiveMatch($scorecardDatainfo)
                ? $this->cricbuzzApi->commentary($id)
                : [];
            $commentaryItems = !empty($commentary)
                ? $this->cricbuzzApi->normalizeCommentaryList($commentary)
                : [];

            // Format overs with conversion for match info
            if (isset($scorecardDatainfo['matchScore']) && is_array($scorecardDatainfo['matchScore'])) {
                foreach ($scorecardDatainfo['matchScore'] as $teamKey => $teamScore) {
                    foreach (['inngs1', 'inngs2'] as $innings) {
                        if (isset($teamScore[$innings]['overs'])) {
                            $originalOver = $teamScore[$innings]['overs'];
                            $scorecardDatainfo['matchScore'][$teamKey][$innings]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                            $scorecardDatainfo['matchScore'][$teamKey][$innings]['overs_original'] = $originalOver;
                        }
                    }
                }
            }

            // Format overs with conversion for scorecard
            if (isset($scorecardData['scorecard']) && is_array($scorecardData['scorecard'])) {
                foreach ($scorecardData['scorecard'] as $index => $scorecard) {
                    if (isset($scorecard['overs'])) {
                        $originalOver = $scorecard['overs'];
                        $scorecardData['scorecard'][$index]['overs_display'] = $this->cricbuzzApi->formatOverDisplay($originalOver)['display'];
                        $scorecardData['scorecard'][$index]['overs_original'] = $originalOver;
                    }
                }
            }

            $seriesId = $scorecardDatainfo['seriesid'] ?? $scorecardDatainfo['seriesId'] ?? '';
            $hasPointTable = false;
            if (!empty($seriesId)) {
                $hasPointTable = $this->cricbuzzApi->hasPointsTable(
                    $this->cricbuzzApi->pointsTable($seriesId)
                );
            }
        } catch (\Exception $e) {
            $scorecardDatainfo = [];
            $scorecardData = [];
            $teamsData = [];
            $commentary = [];
            $commentaryItems = [];
            $hasPointTable = false;
            $errorMsg = $e->getMessage();

            return view('matchdetail', compact(
                'scorecardDatainfo',
                'scorecardData',
                'teamsData',
                'commentary',
                'commentaryItems',
                'hasPointTable',
                'matchState',
                'tab',
                'errorMsg'
            ));
        }

        return view('matchdetail', compact(
            'scorecardDatainfo',
            'scorecardData',
            'teamsData',
            'commentary',
            'commentaryItems',
            'hasPointTable',
            'matchState',
            'tab'
        ));
    }

    public function showSeriesPoints($id)
    {
        $pointTableUrl = env('CriBase_Url')."stats/v1/series/{$id}/points-table";
        $statsUrl = env('CriBase_Url')."stats/v1/series/{$id}?statsType=mostRuns";
        if ($pointTableUrl) {
            $headers = [
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ];

            $pointTableResponse = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)->get($pointTableUrl);
            $statsResponse = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)->get($statsUrl);

            $pointtable = $pointTableResponse->json();
        // echo "<pre>"; print_r($pointtable);die;

            $statsData = $statsResponse->json();
        } else {
            $pointtable = [];
            $statsData = [];
            $errorMsg = $e->getMessage();
            return view('stats', compact('pointtable', 'statsData', 'errorMsg'));
        }

        $activeTab = 'points';
        return view('stats', compact('pointtable', 'statsData', 'activeTab'));
    }
    public function stats($id)
    {
        $statsUrl = env('CriBase_Url')."stats/v1/series/{$id}?statsType=mostRuns";
        $pointTableUrl = env('CriBase_Url')."stats/v1/series/{$id}/points-table";
        if ($statsUrl) {
            $headers = [
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ];

            $statsResponse = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)->get($statsUrl);
            $pointTableResponse = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)->get($pointTableUrl);

            $statsData = $statsResponse->json();
            $pointtable = $pointTableResponse->json();
        } else {
            $statsData = [];
            $pointtable = [];
            $errorMsg = $e->getMessage();
            return view('stats', compact('statsData', 'pointtable', 'errorMsg'));
        }

        $activeTab = 'stats';
        return view('stats', compact('statsData', 'pointtable', 'activeTab'));
    }
    public function teamsinternational(){
        try {
            $apiUrl = env('CriBase_Url')."teams/v1/international";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamsinternational = $response->json();
            if (!is_array($teamsinternational)) {
                $teamsinternational = [];
            }
        } catch (\Exception $e) {
            $teamsinternational = [];
            $errorMsg = $e->getMessage();
            return view('teams', compact('teamsinternational', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsinternational);die;
        return view('teams', compact('teamsinternational'));
    }
    public function teamsdomestic(){
        try {
            $apiUrl = env('CriBase_Url')."teams/v1/domestic";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamsDomestic = $response->json();
            if (!is_array($teamsDomestic)) {
                $teamsDomestic = [];
            }
        } catch (\Exception $e) {
            $teamsDomestic = [];
            $errorMsg = $e->getMessage();
            return view('teams', compact('teamsDomestic', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsDomestic);die;
        return view('teams', compact('teamsDomestic'));
    }
    public function teamswomens(){
        try {
            $apiUrl = env('CriBase_Url')."teams/v1/women";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamsWomens = $response->json();
            if (!is_array($teamsWomens)) {
                $teamsWomens = [];
            }
        } catch (\Exception $e) {
            $teamsWomens = [];
            $errorMsg = $e->getMessage();
            return view('teams', compact('teamsWomens', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsWomens);die;
        return view('teams', compact('teamsWomens'));
    }
    public function teamsleague(){
        try {
            $apiUrl = env('CriBase_Url')."teams/v1/league";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamsleague = $response->json();
            if (!is_array($teamsleague)) {
                $teamsleague = [];
            }
        } catch (\Exception $e) {
            $teamsleague = [];
            $errorMsg = $e->getMessage();
            return view('teams', compact('teamsleague', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsleague);die;
        return view('teams', compact('teamsleague'));
    }

    public function teamDetail($id)
    {
        try {
            $apiUrl = env('CriBase_Url') . "teams/v1/{$id}";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamDetail = $response->json();
        } catch (\Exception $e) {
            $teamDetail = [];
            $errorMsg = $e->getMessage();
            return view('team_detail', compact('teamDetail', 'errorMsg'));
        }

        return view('team_detail', compact('teamDetail'));
    }
    // public function news(){
    //     return view('news');
        
    // }
   
   //  Sduling_upcoming match International
    public function sdulinginternational(){
        try {
            $apiUrl = env('CriBase_Url')."schedule/v1/international";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $sdulinginternational = $response->json();
            if (!is_array($sdulinginternational)) {
                $sdulinginternational = [];
            }
        } catch (\Exception $e) {
            $sdulinginternational = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulinginternational', 'errorMsg'));
        }
            // echo "<pre>";print_r($sdulinginternational);die;
        return view('sduling', compact('sdulinginternational'));
    }
   //  Sduling_upcoming match domestic
    public function sdulingdomestic(){
        try {
            $apiUrl = env('CriBase_Url')."schedule/v1/domestic";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $sdulingdomestic = $response->json();
            if (!is_array($sdulingdomestic)) {
                $sdulingdomestic = [];
            }
        } catch (\Exception $e) {
            $sdulingdomestic = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulingdomestic', 'errorMsg'));
        }
            // echo "<pre>";print_r($sdulinginternational);die;
        return view('sduling', compact('sdulingdomestic'));
    }
    public function sdulingwomen(){
        try {
            $apiUrl = env('CriBase_Url')."schedule/v1/women";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $sdulingwomen = $response->json();
            if (!is_array($sdulingwomen)) {
                $sdulingwomen = [];
            }
        } catch (\Exception $e) {
            $sdulingwomen = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulingwomen', 'errorMsg'));
        }
            // echo "<pre>";print_r($sdulingwomen);die;
        return view('sduling', compact('sdulingwomen'));
    }
    public function sdulingleague(){
        try {
            $apiUrl = env('CriBase_Url')."schedule/v1/league";
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $sdulingleague = $response->json();
            if (!is_array($sdulingleague)) {
                $sdulingleague = [];
            }
        } catch (\Exception $e) {
            $sdulingleague = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulingleague', 'errorMsg'));
        }
            // echo "<pre>";print_r($sdulingleague);die;
        return view('sduling', compact('sdulingleague'));
    }

    

    // all news functions
    public function newscat(Request $request)
    {
        $headers = [
            'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
            'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
            'Content-Type'    => 'application/json',
        ];

        if ($headers) {
            $catResponse = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)->get(env('CriBase_Url') . "news/v1/cat");
            $newscat = $catResponse->json();

            $categories = isset($newscat['storyType']) && is_array($newscat['storyType'])
                ? $newscat['storyType']
                : [];

            $activeCategoryId = $request->query('cat');
            if (empty($activeCategoryId) && !empty($categories)) {
                $activeCategoryId = $categories[0]['id'] ?? null;
            }

            $newsItems = [];
            if (!empty($activeCategoryId)) {
                $listResponse = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)->get(env('CriBase_Url') . "news/v1/cat/{$activeCategoryId}");
                $newsData = $listResponse->json();
                $newsItems = isset($newsData['storyList']) && is_array($newsData['storyList'])
                    ? $newsData['storyList']
                    : [];
            }

            return view('news', compact('newscat', 'categories', 'activeCategoryId', 'newsItems'));
        } else {
            $newscat = [];
            $categories = [];
            $activeCategoryId = null;
            $newsItems = [];
            $errorMsg = $e->getMessage();
            return view('news', compact('newscat', 'categories', 'activeCategoryId', 'newsItems', 'errorMsg'));
        }
    }

    public function newscatdetail($id, $slug = null)
    {
        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get(env('CriBase_Url') . "news/v1/detail/{$id}");

            $newsDetails = $response->json();
        } catch (\Exception $e) {
            $newsDetails = [];
            $errorMsg = $e->getMessage();
            return view('news_details', compact('newsDetails', 'errorMsg'));
        }

        return view('news_details', compact('newsDetails'));
    }


    public function icc_ranking(Request $request, $gender = null, $category = null, $format = null)
    {
        $pathGender = $gender;
        if (empty($pathGender)) {
            $pathGender = request()->is('icc-ranking/womens') ? 'womens' : 'mens';
        }

        $gender = strtolower($pathGender) === 'womens' ? 'womens' : 'mens';
        $category = strtolower($category ?: $request->query('category', 'allrounders'));
        $defaultFormat = $gender === 'womens' ? 'odi' : 'test';
        
        // Get format from query string or parameter, but validate it against the gender's allowed formats
        $requestedFormat = strtolower($format ?: $request->query('format', $defaultFormat));
        $allowedFormats = $gender === 'womens' ? ['odi', 't20'] : ['test', 'odi', 't20'];
        
        // If the requested format is not valid for this gender, use the default
        if (!in_array($requestedFormat, $allowedFormats, true)) {
            $format = $defaultFormat;
        } else {
            $format = $requestedFormat;
        }

        $allowedCategories = ['batsmen', 'bowlers', 'allrounders', 'teams'];

        if (!in_array($category, $allowedCategories, true)) {
            $category = 'allrounders';
        }

        try {
            $rankingData = $this->fetchIccRankingData($gender, $category, $format);
            $selectedItem = null;
            // echo '<pre>'; print_r($rankingData);die;
            return view('icc_ranking', compact('rankingData', 'gender', 'category', 'format', 'selectedItem'));
        } catch (\Exception $e) {
            $rankingData = [];
            $selectedItem = null;
            $errorMsg = $e->getMessage();
            return view('icc_ranking', compact('rankingData', 'gender', 'category', 'format', 'selectedItem', 'errorMsg'));
        }
    }

    public function icc_ranking_detail(Request $request, $gender, $category, $format, $id, $slug = null)
    {
        $gender = strtolower($gender) === 'womens' ? 'womens' : 'mens';
        $category = strtolower($category);
        $requestedFormat = strtolower($format);

        $allowedCategories = ['batsmen', 'bowlers', 'allrounders', 'teams'];
        $defaultFormat = $gender === 'womens' ? 'odi' : 'test';
        $allowedFormats = $gender === 'womens' ? ['odi', 't20'] : ['test', 'odi', 't20'];

        if (!in_array($category, $allowedCategories, true)) {
            $category = 'allrounders';
        }

        // If the requested format is not valid for this gender, use the default
        if (!in_array($requestedFormat, $allowedFormats, true)) {
            $format = $defaultFormat;
        } else {
            $format = $requestedFormat;
        }

        try {
            $rankingData = $this->fetchIccRankingData($gender, $category, $format);
            $selectedItem = null;

            if (isset($rankingData['rank']) && is_array($rankingData['rank'])) {
                foreach ($rankingData['rank'] as $rankItem) {
                    $item = is_array($rankItem) && isset($rankItem[$category]) && is_array($rankItem[$category])
                        ? $rankItem[$category]
                        : [];
                    $itemId = (string)($item['id'] ?? $item['teamId'] ?? '');

                    if ($itemId !== '' && $itemId === (string)$id) {
                        $selectedItem = $rankItem;
                        break;
                    }
                }
            }

            return view('icc_ranking', compact('rankingData', 'gender', 'category', 'format', 'selectedItem'));
        } catch (\Exception $e) {
            $rankingData = [];
            $selectedItem = null;
            $errorMsg = $e->getMessage();
            return view('icc_ranking', compact('rankingData', 'gender', 'category', 'format', 'selectedItem', 'errorMsg'));
        }
    }

    private function fetchIccRankingData($gender, $category, $format)
    {
        try {
            $apiUrl = env('CriBase_Url') . "stats/v1/rankings/{$category}";
            $queryParams = [];

            if ($gender === 'womens') {
                $queryParams['isWomen'] = '1';
            } else {
                $queryParams['isMen'] = '0';
            }

            $queryParams['formatType'] = $format;

            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl, $queryParams);

            $data = $response->json();

            // Normalize the response to handle different API structures
            if (isset($data['rank']) && is_array($data['rank'])) {
                // Ensure rank data is properly structured
                foreach ($data['rank'] as &$rankItem) {
                    if (is_array($rankItem)) {
                        // Normalize category data keys
                        foreach (['batsmen', 'bowlers', 'allrounders', 'teams', 'batsman', 'bowler', 'allrounder', 'team'] as $key) {
                            if (isset($rankItem[$key]) && is_array($rankItem[$key])) {
                                // Ensure name field exists
                                if (!isset($rankItem[$key]['name']) && isset($rankItem[$key]['teamName'])) {
                                    $rankItem[$key]['name'] = $rankItem[$key]['teamName'];
                                }
                                if (!isset($rankItem[$key]['name']) && isset($rankItem[$key]['playerName'])) {
                                    $rankItem[$key]['name'] = $rankItem[$key]['playerName'];
                                }
                            }
                        }
                    }
                }
            }

            return $data;
        } catch (\Exception $e) {
            return ['rank' => [], 'error' => $e->getMessage()];
        }
    }
}
