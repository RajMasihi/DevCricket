<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Cricketlivescorecontroller extends Controller
{
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
        } else {
            // Pass error msg to view or fallback mode
            $serieslists = [];
            $errorMsg = $e->getMessage();
            return view('serieslist', compact('serieslists', 'errorMsg'));
        }
            // echo "<pre>";print_r($serieslists);die;
        return view('serieslist', compact('serieslists'));
    }
    // serires match function end

    // Live match function start

    public function result()
    {
        {
            $apiUrl = env('CriBase_Url').'matches/v1/recent';
            // $apiUrl = env('CriBase_Url').'live';
            if ($apiUrl) {
                $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                    'Content-Type'      => 'application/json',
                    'x-rapidapi-host'   => 'cricbuzz-cricket2.p.rapidapi.com',
                    'x-rapidapi-key'    => env('RAPIDAPI_KEY'), // Never hardcode your API key!
                ])->get($apiUrl);
    
                if ($response->failed()) {
                    return view('index', ['matches' => [], 'error' => 'No Match data!']);
                }
    
                $data = $response->json();
                $result = [];
    
                if (isset($data['matches']) && is_array($data['matches'])) {
                    $result = $data['matches'];
                } elseif (isset($data['typeMatches']) && is_array($data['typeMatches'])) {
                    foreach ($data['typeMatches'] as $typeMatch) {
                        if (isset($typeMatch['seriesMatches']) && is_array($typeMatch['seriesMatches'])) {
                            foreach ($typeMatch['seriesMatches'] as $seriesObj) {
                                if (
                                    isset($seriesObj['seriesAdWrapper'], $seriesObj['seriesAdWrapper']['matches']) &&
                                    is_array($seriesObj['seriesAdWrapper']['matches'])
                                ) {
                                    foreach ($seriesObj['seriesAdWrapper']['matches'] as $m) {
                                        // Get matchInfo and matchScore arrays if present
                                        $matchInfo = isset($m['matchInfo']) && is_array($m['matchInfo']) ? $m['matchInfo'] : [];
                                        $matchScore = isset($m['matchScore']) && is_array($m['matchScore']) ? $m['matchScore'] : [];
    
                                        // Push match details as array: preserve both matchInfo and matchScore fully, top-level
                                        $result[] = [
                                            'matchInfo'  => $matchInfo,
                                            'matchScore' => $matchScore,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
                    // echo "<pre>";print_r($result);die;
                return view('index', ['result' => $result, 'error' => null]);
            } else {
                return view('index', [
                    'result' => [],

                    'error' => 'Error fetching data: ' .'Yoy can Reload Page its Session time out.',

                ]);
            }
        }
    }

    public function CricketliveScores()
    {
        // $apiUrl = env('CriBase_Url').'recent';
        $apiUrl = env('CriBase_Url').'matches/v1/live';
        if($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Content-Type'    => 'application/json',
                'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'x-rapidapi-key'  => env('RAPIDAPI_KEY'),
            ])->get($apiUrl);
                // echo $response;
            if ($response->failed()) {
                return view('index', ['matches' => [], 'error' => 'No Match data!']);
            }

            $data = $response->json();
            $matches = [];
            if (isset($data['matches']) && is_array($data['matches'])) {
                $matches = $data['matches'];
            } elseif (isset($data['typeMatches']) && is_array($data['typeMatches'])) {
                foreach ($data['typeMatches'] as $typeMatch) {
                    if (isset($typeMatch['seriesMatches']) && is_array($typeMatch['seriesMatches'])) {
                        foreach ($typeMatch['seriesMatches'] as $seriesObj) {
                            if (
                                isset($seriesObj['seriesAdWrapper'], $seriesObj['seriesAdWrapper']['matches']) &&
                                is_array($seriesObj['seriesAdWrapper']['matches'])
                            ) {
                                foreach ($seriesObj['seriesAdWrapper']['matches'] as $m) {
                                    // Get matchInfo and matchScore arrays if present
                                    $matchInfo = isset($m['matchInfo']) && is_array($m['matchInfo']) ? $m['matchInfo'] : [];
                                    $matchScore = isset($m['matchScore']) && is_array($m['matchScore']) ? $m['matchScore'] : [];

                                    // Push match details as array: preserve both matchInfo and matchScore fully, top-level
                                    $matches[] = [
                                        'matchInfo'  => $matchInfo,
                                        'matchScore' => $matchScore,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
                // echo "<pre>";print_r($matches);die;
            return view('index', ['matches' => $matches, 'error' => null]);
        } else {
            return view('index', [
                'matches' => [],

                'error' => 'Error fetching data: ' .'Yoy can Reload Page its Session time out.',

            ]);
        }
    }

    public function upcoming()
    {
        
            // $apiUrl = env('CriBase_Url').'recent';
            $apiUrl = env('CriBase_Url').'matches/v1/upcoming';
            if ($apiUrl) {
                $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                    'Content-Type'      => 'application/json',
                    'x-rapidapi-host'   => 'cricbuzz-cricket2.p.rapidapi.com',
                    'x-rapidapi-key'    => env('RAPIDAPI_KEY'), // Never hardcode your API key!
                ])->get($apiUrl);
    
                if ($response->failed()) {
                    return view('index', ['matches' => [], 'error' => 'No Match data!']);
                }
    
                $data = $response->json();
                $sduling = [];
    
                if (isset($data['matches']) && is_array($data['matches'])) {
                    $sduling = $data['matches'];
                } elseif (isset($data['typeMatches']) && is_array($data['typeMatches'])) {
                    foreach ($data['typeMatches'] as $typeMatch) {
                        if (isset($typeMatch['seriesMatches']) && is_array($typeMatch['seriesMatches'])) {
                            foreach ($typeMatch['seriesMatches'] as $seriesObj) {
                                if (
                                    isset($seriesObj['seriesAdWrapper'], $seriesObj['seriesAdWrapper']['matches']) &&
                                    is_array($seriesObj['seriesAdWrapper']['matches'])
                                ) {
                                    foreach ($seriesObj['seriesAdWrapper']['matches'] as $m) {
                                        // Get matchInfo and matchScore arrays if present
                                        $matchInfo = isset($m['matchInfo']) && is_array($m['matchInfo']) ? $m['matchInfo'] : [];
                                        $matchScore = isset($m['matchScore']) && is_array($m['matchScore']) ? $m['matchScore'] : [];
    
                                        // Push match details as array: preserve both matchInfo and matchScore fully, top-level
                                        $sduling[] = [
                                            'matchInfo'  => $matchInfo,
                                            'matchScore' => $matchScore,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
                    // echo "<pre>";print_r($sduling);die;
                return view('index', ['sduling' => $sduling, 'error' => null]);
            } else {
                return view('index', [
                    'sduling' => [],

                    'error' => 'Error fetching data: ' .'Yoy can Reload Page its Session time out.',

                ]);
            }
        
    }
    // Live match function end

    // Match details scoreboard 
    public function matchdetailscore($id)
    {
        $apiUrlscard = env('CriBase_Url')."mcenter/v1/{$id}/scard";
        if ($apiUrlscard) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Accept' => 'application/json',
                'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'x-rapidapi-key' => env('RAPIDAPI_KEY'),
                ])->get($apiUrlscard);
                // info
            $apiUrlscardinfo = env('CriBase_Url')."mcenter/v1/{$id}";
            $responseinfo = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                    'Accept' => 'application/json',
                    'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com',
                    'x-rapidapi-key' => env('RAPIDAPI_KEY'),
                    ])->get($apiUrlscardinfo);
            
            $scorecardDatainfo = $responseinfo->json();
            $scorecardData = $response->json();
        } else {
            // Pass error msg to view or fallback mode
            $scorecardData = [];
            $errorMsg = $e->getMessage();
            return view('matchdetail', compact('scorecardData', 'errorMsg'));
        }
            // echo "<pre>";print_r($scorecardData);die;
        return view('matchdetail', compact('scorecardData','scorecardDatainfo'));
    }
    public function matchdetailinforme($id)
    {
        $apiUrl = env('CriBase_Url')."mcenter/v1/{$id}";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);
            
            $scorecardDatainfo = $response->json();
        } else {
            $scorecardDatainfo = [];
            $errorMsg = $e->getMessage();
            return view('matchdetail', compact('scorecardDatainfo', 'errorMsg'));
        }
            // echo "<pre>";print_r($scorecardDatainfo);die;
        return view('matchdetail', compact('scorecardDatainfo'));
    }
    public function matchdetailplayer($id)
    {
        $apiUrl = env('CriBase_Url')."mcenter/v1/{$id}/teams";
        if ($apiUrl) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'X-Rapidapi-Key' => env('RAPIDAPI_KEY'),
                'X-Rapidapi-Host' => 'cricbuzz-cricket2.p.rapidapi.com',
                'Content-Type'    => 'application/json',
            ])->get($apiUrl);
            $apiUrlscardinfo = env('CriBase_Url')."mcenter/v1/{$id}";
            $responseinfo = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                    'Accept' => 'application/json',
                    'x-rapidapi-host' => 'cricbuzz-cricket2.p.rapidapi.com',
                    'x-rapidapi-key' => env('RAPIDAPI_KEY'),
                    ])->get($apiUrlscardinfo);
            
            $scorecardDatainfo = $responseinfo->json();
            $teamsData = $response->json();
        } else {
            $teamsData = [];
            $errorMsg = $e->getMessage();
            return view('matchdetail', compact('teamsData', 'errorMsg'));
        }
            // echo "<pre>";print_r($teamsData);die;
        return view('matchdetail', compact('teamsData','scorecardDatainfo'));
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