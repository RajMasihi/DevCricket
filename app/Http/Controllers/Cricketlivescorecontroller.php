<?php

namespace App\Http\Controllers;

use App\Services\CricbuzzApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Cricketlivescorecontroller extends Controller
{
    public function __construct(private CricbuzzApiService $cricbuzzApi) {}
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
        $matches = $this->cricbuzzApi->liveMatches();
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
        return view('index', [
            'matches' => $matches,
            'error' => null,
        ]);

    }

    public function upcoming()
    {
        $matches = $this->cricbuzzApi->upcomingMatches();

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

        return view('index', [
            'sduling' => $matches,
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
        $apiUrl = env('CriBase_Url')."teams/v1/international";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamsinternational = $response->json();
        } else {
            $teamsinternational = [];
            $errorMsg = $e->getMessage();
            return view('teams', compact('teamsinternational', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsinternational);die;
        return view('teams', compact('teamsinternational'));
    }
    public function teamsdomestic(){
        $apiUrl = env('CriBase_Url')."teams/v1/domestic";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamsDomestic = $response->json();
        } else {
            $teamsDomestic = [];
            $errorMsg = $e->getMessage();
            return view('teams', compact('teamsDomestic', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsDomestic);die;
        return view('teams', compact('teamsDomestic'));
    }
    public function teamswomens(){
        $apiUrl = env('CriBase_Url')."teams/v1/women";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamsWomens = $response->json();
        } else {
            $teamsWomens = [];
            $errorMsg = $e->getMessage();
            return view('teams', compact('teamsWomens', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsWomens);die;
        return view('teams', compact('teamsWomens'));
    }
    public function teamsleague(){
        $apiUrl = env('CriBase_Url')."teams/v1/league";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $teamsleague = $response->json();
        } else {
            $teamsleague = [];
            $errorMsg = $e->getMessage();
            return view('teams', compact('teamsleague', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsleague);die;
        return view('teams', compact('teamsleague'));
    }
    // public function news(){
    //     return view('news');
        
    // }
   
   //  Sduling_upcoming match International 
    public function sdulinginternational(){
        $apiUrl = env('CriBase_Url')."schedule/v1/International?lastTime=1729555200000";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $sdulinginternational = $response->json();
        } else {
            $sdulinginternational = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulinginternational', 'errorMsg'));
        }
            // echo "<pre>";print_r($sdulinginternational);die;
        return view('sduling', compact('sdulinginternational'));
    }
   //  Sduling_upcoming match domestic 
    public function sdulingdomestic(){
        $apiUrl = env('CriBase_Url')."schedule/v1/domestic?lastTime=1729555200000";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $sdulingdomestic = $response->json();
        } else {
            $sdulingdomestic = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulingdomestic', 'errorMsg'));
        }
            // echo "<pre>";print_r($sdulinginternational);die;
        return view('sduling', compact('sdulingdomestic'));
    }
    public function sdulingwomen(){
        $apiUrl = env('CriBase_Url')."schedule/v1/women?lastTime=1729555200000";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $sdulingwomen = $response->json();
        } else {
            $sdulingwomen = [];
            $errorMsg = $e->getMessage();
            return view('sduling', compact('sdulingwomen', 'errorMsg'));
        }
            // echo "<pre>";print_r($sdulingwomen);die;
        return view('sduling', compact('sdulingwomen'));
    }
    public function sdulingleague(){
        $apiUrl = env('CriBase_Url')."schedule/v1/league?lastTime=1729555200000";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);

            $sdulingleague = $response->json();
        } else {
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

    public function newscatdetail($id, $name = null)
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
        $format = strtolower($format ?: $request->query('format', $defaultFormat));

        $allowedCategories = ['batsmen', 'bowlers', 'allrounders', 'teams'];
        $allowedFormats = $gender === 'womens' ? ['odi', 't20'] : ['test', 'odi', 't20'];

        if (!in_array($category, $allowedCategories, true)) {
            $category = 'allrounders';
        }

        if (!in_array($format, $allowedFormats, true)) {
            $format = $defaultFormat;
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

    public function icc_ranking_detail(Request $request, $gender, $category, $format, $id, $name = null)
    {
        $gender = strtolower($gender) === 'womens' ? 'womens' : 'mens';
        $category = strtolower($category);
        $format = strtolower($format);

        $allowedCategories = ['batsmen', 'bowlers', 'allrounders', 'teams'];
        $defaultFormat = $gender === 'womens' ? 'odi' : 'test';
        $allowedFormats = $gender === 'womens' ? ['odi', 't20'] : ['test', 'odi', 't20'];

        if (!in_array($category, $allowedCategories, true)) {
            $category = 'allrounders';
        }

        if (!in_array($format, $allowedFormats, true)) {
            $format = $defaultFormat;
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
        
        $isMen = $gender === 'mens' ? '0' : '1';
        if($gender == 'womens'){
        $apiUrl = env('CriBase_Url') . "stats/v1/rankings/{$category}?isWomen={$isMen}&formatType={$format}";
        }else{
        $apiUrl = env('CriBase_Url') . "stats/v1/rankings/{$category}?isMen={$isMen}&formatType={$format}";
        }
        $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
            'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
            'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
            'Content-Type'    => 'application/json',
        ])->get($apiUrl);
            // echo "<pre>";print_r($apiUrl);die;
        return $response->json();
    }
}
