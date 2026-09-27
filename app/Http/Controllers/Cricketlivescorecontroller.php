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
        return view('about', [
            'pageTitle' => 'About Us - Criclivem Cricket Platform',
            'metaDescription' => 'Learn about Criclivem - your premier destination for live cricket scores, match updates, schedules, news, and ICC rankings. Discover our mission to bring cricket to fans worldwide.',
            'metaKeywords' => 'about criclivem, cricket platform, live cricket scores, cricket news, about us, cricket website'
        ]);
    }

    public function contact()
    {
        return view('contact', [
            'pageTitle' => 'Contact Us - Criclivem',
            'metaDescription' => 'Get in touch with Criclivem for feedback, partnerships, or general inquiries. Contact our team for any questions about our cricket platform and services.',
            'metaKeywords' => 'contact criclivem, cricket support, feedback, partnerships, cricket website contact'
        ]);
    }

    public function privacy()
    {
        return view('privacy', [
            'pageTitle' => 'Privacy Policy - Criclivem',
            'metaDescription' => 'Read Criclivem privacy policy to understand how we collect, use, and protect your personal data. Learn about our commitment to user privacy and data security.',
            'metaKeywords' => 'privacy policy, data protection, user privacy, criclivem privacy, cricket website privacy'
        ]);
    }

    public function gallery()
    {
        return view('gallery', [
            'pageTitle' => 'Cricket Gallery - Images & Videos | Criclivem',
            'metaDescription' => 'Explore our cricket gallery featuring player images, match photos, cricket moments, and videos from international and domestic cricket matches around the world.',
            'metaKeywords' => 'cricket gallery, cricket images, cricket photos, cricket videos, player photos, match images'
        ]);
    }

    public function sitemap()
    {
        return response()->view('sitemap')->header('Content-Type', 'application/xml');
    }

    // serires match function start
    public function series()
    {
        $pageTitle = 'Cricket Series - International & Domestic | Criclivem';
        $metaDescription = 'Browse all cricket series including international tours, domestic leagues, and tournaments. Get complete series information, schedules, and match details.';
        $metaKeywords = 'cricket series, international cricket series, domestic cricket leagues, cricket tournaments, cricket tours, series schedule, ICC series';
        
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
            $errorMsg = 'API URL not configured';
            return view('series', compact('seriess', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($seriess);die;
        return view('series', compact('seriess', 'pageTitle', 'metaDescription', 'metaKeywords'));
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
            
            // Generate dynamic SEO metadata
            $seriesName = $serieslists['name'] ?? 'Cricket Series';
            $pageTitle = "{$seriesName} - Series Details | Criclivem";
            $metaDescription = "View complete details of {$seriesName} cricket series including match schedule, teams, fixtures, and point table. Get all information about this cricket tournament.";
            $metaKeywords = "{$seriesName}, cricket series details, cricket tournament, match schedule, cricket fixtures, series point table";
            
        } else {
            // Pass error msg to view or fallback mode
            $serieslists = [];
            $hasPointTable = false;
            $errorMsg = 'API URL not configured';
            $pageTitle = 'Series Details - Criclivem';
            $metaDescription = 'View cricket series details including match schedule, teams, and fixtures.';
            $metaKeywords = 'cricket series details, cricket tournament, match schedule';
            return view('serieslist', compact('serieslists', 'hasPointTable', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($serieslists);die;
        return view('serieslist', compact('serieslists', 'hasPointTable', 'pageTitle', 'metaDescription', 'metaKeywords'));
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
            'pageTitle' => 'Cricket Match Results - Recent Scores | Criclivem',
            'metaDescription' => 'View recent cricket match results, final scores, and match outcomes from international and domestic cricket matches around the world.',
            'metaKeywords' => 'cricket results, match results, cricket scores, final scores, cricket outcomes, recent matches, cricket winners',
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

        // Add match time/status to news items
        foreach ($newsItems as &$newsItem) {
            if (isset($newsItem['story']) && isset($newsItem['story']['matchId'])) {
                $matchId = $newsItem['story']['matchId'];
                // Try to find match time/status from live, recent, or upcoming matches
                $relatedMatch = null;
                
                // Search in live matches
                foreach ($liveMatches as $match) {
                    $matchInfo = $match['matchInfo'] ?? [];
                    if (($matchInfo['matchId'] ?? '') == $matchId) {
                        $relatedMatch = $matchInfo;
                        break;
                    }
                }
                
                // Search in recent matches if not found
                if (!$relatedMatch) {
                    foreach ($recentMatches as $match) {
                        $matchInfo = $match['matchInfo'] ?? [];
                        if (($matchInfo['matchId'] ?? '') == $matchId) {
                            $relatedMatch = $matchInfo;
                            break;
                        }
                    }
                }
                
                // Search in upcoming matches if still not found
                if (!$relatedMatch) {
                    foreach ($upcomingMatches as $match) {
                        $matchInfo = $match['matchInfo'] ?? [];
                        if (($matchInfo['matchId'] ?? '') == $matchId) {
                            $relatedMatch = $matchInfo;
                            break;
                        }
                    }
                }
                
                if ($relatedMatch) {
                    $state = strtolower($relatedMatch['state'] ?? '');
                    $startDate = isset($relatedMatch['startDate']) ? ((int)$relatedMatch['startDate'] / 1000) : null;
                    $matchTime = $startDate ? date('d M, h:i A', $startDate) : '';
                    $status = $relatedMatch['status'] ?? '';
                    
                    $newsItem['story']['matchTime'] = $matchTime;
                    $newsItem['story']['matchStatus'] = $status;
                    $newsItem['story']['matchState'] = $state;
                }
            }
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
            'pageTitle' => 'Live Cricket Scores - Criclivem',
            'metaDescription' => 'Get live cricket scores, ball-by-ball commentary, and match updates from around the world. Follow international matches, domestic leagues, and women cricket in real-time.',
            'metaKeywords' => 'live cricket scores, cricket live score, ball by ball commentary, cricket updates, international cricket, domestic cricket, T20 live score, ODI live score, Test cricket live',
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
            'pageTitle' => 'Cricket Schedule - Upcoming Matches | Criclivem',
            'metaDescription' => 'View upcoming cricket match schedule, fixtures, and start times for international matches, domestic leagues, and tournaments. Plan your cricket viewing with our comprehensive schedule.',
            'metaKeywords' => 'cricket schedule, upcoming matches, cricket fixtures, match schedule, cricket calendar, upcoming cricket, international cricket schedule, domestic cricket fixtures',
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
                    'pageTitle' => 'Match Details - Criclivem',
                    'metaDescription' => 'View detailed cricket match information, scorecard, and live commentary on Criclivem.',
                    'metaKeywords' => 'cricket match details, live scorecard, cricket commentary, match statistics',
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

            // Generate dynamic SEO metadata
            $team1Name = $scorecardDatainfo['team1']['teamName'] ?? 'Team 1';
            $team2Name = $scorecardDatainfo['team2']['teamName'] ?? 'Team 2';
            $matchFormat = strtoupper($scorecardDatainfo['matchFormat'] ?? 'Match');
            $seriesName = $scorecardDatainfo['seriesName'] ?? '';
            $matchStatus = $scorecardDatainfo['status'] ?? '';
            
            $pageTitle = "{$team1Name} vs {$team2Name} - {$matchFormat} Match - {$seriesName} | Criclivem";
            $metaDescription = "Live cricket score: {$team1Name} vs {$team2Name} - {$matchFormat} match in {$seriesName}. Get live scores, ball-by-ball commentary, scorecard, and match statistics. {$matchStatus}";
            $metaKeywords = "{$team1Name} vs {$team2Name}, {$matchFormat} cricket, {$seriesName}, live cricket score, cricket scorecard, ball by ball commentary, {$team1Name}, {$team2Name}";

        } catch (\Exception $e) {
            $scorecardDatainfo = [];
            $scorecardData = [];
            $teamsData = [];
            $commentary = [];
            $commentaryItems = [];
            $hasPointTable = false;
            $errorMsg = $e->getMessage();
            $pageTitle = 'Match Details - Criclivem';
            $metaDescription = 'View detailed cricket match information, scorecard, and live commentary on Criclivem.';
            $metaKeywords = 'cricket match details, live scorecard, cricket commentary, match statistics';
            
            return view('matchdetail', compact(
                'scorecardDatainfo',
                'scorecardData',
                'teamsData',
                'commentary',
                'commentaryItems',
                'hasPointTable',
                'matchState',
                'tab',
                'errorMsg',
                'pageTitle',
                'metaDescription',
                'metaKeywords'
            ));
        }
        // dd($scorecardDatainfo);
        return view('matchdetail', compact(
            'scorecardDatainfo',
            'scorecardData',
            'teamsData',
            'commentary',
            'commentaryItems',
            'hasPointTable',
            'matchState',
            'tab',
            'pageTitle',
            'metaDescription',
            'metaKeywords'
        ));
    }

    public function showSeriesPoints($id)
    {
        $pointTableUrl = env('CriBase_Url')."stats/v1/series/{$id}/points-table";
        $statsUrl = env('CriBase_Url')."stats/v1/series/{$id}?statsType=mostRuns";
        $pageTitle = 'Series Point Table - Criclivem';
        $metaDescription = 'View cricket series point table with team standings, points, and rankings.';
        $metaKeywords = 'cricket point table, series standings, team rankings, cricket points table, tournament standings';
        
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
            $errorMsg = 'API URL not configured';
            return view('stats', compact('pointtable', 'statsData', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }

        $activeTab = 'points';
        return view('stats', compact('pointtable', 'statsData', 'activeTab', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
    public function stats($id)
    {
        $statsUrl = env('CriBase_Url')."stats/v1/series/{$id}?statsType=mostRuns";
        $pointTableUrl = env('CriBase_Url')."stats/v1/series/{$id}/points-table";
        $pageTitle = 'Series Statistics - Criclivem';
        $metaDescription = 'View cricket series statistics including most runs, most wickets, highest scores, and bowling figures.';
        $metaKeywords = 'cricket series stats, cricket statistics, most runs, most wickets, cricket records, series performance';
        
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
            $errorMsg = 'API URL not configured';
            return view('stats', compact('statsData', 'pointtable', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }

        $activeTab = 'stats';
        return view('stats', compact('statsData', 'pointtable', 'activeTab', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
    public function teamsinternational(){
        $pageTitle = 'International Cricket Teams | Criclivem';
        $metaDescription = 'View all international cricket teams, player squads, and team information. Explore national cricket teams from around the world including ICC member nations.';
        $metaKeywords = 'international cricket teams, national cricket teams, ICC teams, cricket squads, cricket team information, world cricket teams';
        
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
            return view('teams', compact('teamsinternational', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($teamsinternational);die;
        return view('teams', compact('teamsinternational', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
    public function teamsdomestic(){
        $pageTitle = 'Domestic Cricket Teams | Criclivem';
        $metaDescription = 'Explore domestic cricket teams from various leagues and tournaments around the world. Get information about county cricket, state teams, and domestic cricket franchises.';
        $metaKeywords = 'domestic cricket teams, county cricket, state cricket teams, cricket franchises, domestic leagues, local cricket teams';
        
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
            return view('teams', compact('teamsDomestic', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($teamsDomestic);die;
        return view('teams', compact('teamsDomestic', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
    public function teamswomens(){
        $pageTitle = 'Women Cricket Teams | Criclivem';
        $metaDescription = 'View all women cricket teams and national squads. Explore women international cricket teams, player information, and women cricket leagues around the world.';
        $metaKeywords = 'women cricket teams, women international cricket, women cricket squads, female cricket teams, women cricket leagues, women national teams';
        
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
            return view('teams', compact('teamsWomens', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($teamsWomens);die;
        return view('teams', compact('teamsWomens', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
    public function teamsleague(){
        $pageTitle = 'Cricket League Teams | Criclivem';
        $metaDescription = 'Explore cricket league teams and franchises from T20 leagues around the world. Get information about IPL teams, BBL franchises, CPL teams, and other domestic cricket leagues.';
        $metaKeywords = 'cricket league teams, T20 league franchises, IPL teams, BBL teams, CPL teams, cricket franchises, domestic league teams';
        
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
            return view('teams', compact('teamsleague', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($teamsleague);die;
        return view('teams', compact('teamsleague', 'pageTitle', 'metaDescription', 'metaKeywords'));
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
            
            // Generate dynamic SEO metadata
            $teamName = $teamDetail['teamName'] ?? 'Cricket Team';
            $pageTitle = "{$teamName} - Team Details | Criclivem";
            $metaDescription = "View complete details of {$teamName} cricket team including player profiles, match history, upcoming fixtures, and team statistics. Explore the squad and performance records.";
            $metaKeywords = "{$teamName}, cricket team details, {$teamName} players, cricket squad, team statistics, {$teamName} matches";
            
        } catch (\Exception $e) {
            $teamDetail = [];
            $errorMsg = $e->getMessage();
            $pageTitle = 'Team Details - Criclivem';
            $metaDescription = 'View cricket team details including player profiles, match history, and team statistics.';
            $metaKeywords = 'cricket team details, cricket squad, team statistics, player profiles';
            return view('team_detail', compact('teamDetail', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }

        return view('team_detail', compact('teamDetail', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
    // public function news(){
    //     return view('news');
        
    // }
   
   //  Sduling_upcoming match International
    public function sdulinginternational(){
        $pageTitle = 'International Cricket Schedule | Criclivem';
        $metaDescription = 'View complete international cricket schedule with match fixtures, dates, and venues. Stay updated with upcoming international cricket series and tournaments.';
        $metaKeywords = 'international cricket schedule, cricket fixtures, international cricket calendar, upcoming international matches, cricket series schedule';
        
        try {
            $apiUrl = env('CriBase_Url')."schedule/v1/International?lastTime=1729555200000";
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
            // dd($sdulinginternational);
        } catch (\Exception $e) {
            $sdulinginternational = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulinginternational', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($sdulinginternational);die;
        return view('sduling', compact('sdulinginternational', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
   //  Sduling_upcoming match domestic
    public function sdulingdomestic(){
        $pageTitle = 'Domestic Cricket Schedule | Criclivem';
        $metaDescription = 'View domestic cricket schedule with match fixtures, dates, and venues. Stay updated with upcoming domestic cricket leagues and tournaments around the world.';
        $metaKeywords = 'domestic cricket schedule, cricket fixtures, domestic cricket calendar, upcoming domestic matches, cricket league schedule';
        
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
        //    dd($sdulingdomestic);
        } catch (\Exception $e) {
            $sdulingdomestic = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulingdomestic', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($sdulinginternational);die;
        return view('sduling', compact('sdulingdomestic', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
    public function sdulingwomen(){
        $pageTitle = 'Women Cricket Schedule | Criclivem';
        $metaDescription = 'View women cricket schedule with match fixtures, dates, and venues. Stay updated with upcoming women international cricket series and tournaments.';
        $metaKeywords = 'women cricket schedule, women cricket fixtures, women cricket calendar, upcoming women matches, women cricket series';
        
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
            return view('sduling', compact('sdulingwomen', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($sdulingwomen);die;
        return view('sduling', compact('sdulingwomen', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }
    public function sdulingleague(){
        $pageTitle = 'Cricket League Schedule | Criclivem';
        $metaDescription = 'View cricket league schedule with match fixtures, dates, and venues. Stay updated with upcoming T20 leagues, domestic tournaments, and franchise cricket around the world.';
        $metaKeywords = 'cricket league schedule, T20 league fixtures, cricket tournament calendar, upcoming league matches, franchise cricket schedule';
        
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
            return view('sduling', compact('sdulingleague', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }
            // echo "<pre>";print_r($sdulingleague);die;
        return view('sduling', compact('sdulingleague', 'pageTitle', 'metaDescription', 'metaKeywords'));
    }

    

    // all news functions
    public function newscat(Request $request)
    {
        $headers = [
            'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
            'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
            'Content-Type'    => 'application/json',
        ];

        $pageTitle = 'Cricket News - Latest Updates | Criclivem';
        $metaDescription = 'Get the latest cricket news, match updates, player interviews, and cricket analysis from around the world. Stay informed with breaking cricket news and in-depth coverage.';
        $metaKeywords = 'cricket news, latest cricket updates, cricket interviews, cricket analysis, breaking cricket news, cricket headlines, sports news';

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

            return view('news', compact('newscat', 'categories', 'activeCategoryId', 'newsItems', 'pageTitle', 'metaDescription', 'metaKeywords'));
        } else {
            $newscat = [];
            $categories = [];
            $activeCategoryId = null;
            $newsItems = [];
            $errorMsg = $e->getMessage();
            return view('news', compact('newscat', 'categories', 'activeCategoryId', 'newsItems', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
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
            
            // Generate dynamic SEO metadata
            $headline = $newsDetails['story']['hline'] ?? 'Cricket News';
            $intro = $newsDetails['story']['intro'] ?? '';
            $pageTitle = "{$headline} - Cricket News | Criclivem";
            $metaDescription = !empty($intro) ? substr(strip_tags($intro), 0, 160) : "Read latest cricket news: {$headline}. Get detailed coverage and analysis of cricket events from around the world.";
            $metaKeywords = "{$headline}, cricket news, cricket updates, sports news, cricket analysis";
            
        } catch (\Exception $e) {
            $newsDetails = [];
            $errorMsg = $e->getMessage();
            $pageTitle = 'Cricket News Details - Criclivem';
            $metaDescription = 'Read detailed cricket news coverage and analysis from Criclivem.';
            $metaKeywords = 'cricket news details, cricket analysis, sports news';
            return view('news_details', compact('newsDetails', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
        }

        return view('news_details', compact('newsDetails', 'pageTitle', 'metaDescription', 'metaKeywords'));
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

        // Generate dynamic SEO metadata
        $genderTitle = ucfirst($gender);
        $categoryTitle = ucfirst($category);
        $formatTitle = strtoupper($format);
        $pageTitle = "ICC {$genderTitle} Rankings - {$categoryTitle} {$formatTitle} | Criclivem";
        $metaDescription = "View official ICC {$genderTitle} rankings for {$categoryTitle} in {$formatTitle} cricket. Get the latest player and team rankings, points, and position changes.";
        $metaKeywords = "ICC {$genderTitle} rankings, {$categoryTitle} rankings, {$formatTitle} cricket rankings, cricket player rankings, cricket team rankings, ICC rankings table";

        try {
            $rankingData = $this->fetchIccRankingData($gender, $category, $format);
            $selectedItem = null;
            // echo '<pre>'; print_r($rankingData);die;
            return view('icc_ranking', compact('rankingData', 'gender', 'category', 'format', 'selectedItem', 'pageTitle', 'metaDescription', 'metaKeywords'));
        } catch (\Exception $e) {
            $rankingData = [];
            $selectedItem = null;
            $errorMsg = $e->getMessage();
            return view('icc_ranking', compact('rankingData', 'gender', 'category', 'format', 'selectedItem', 'errorMsg', 'pageTitle', 'metaDescription', 'metaKeywords'));
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
