<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;
class Cricketlivescorecontroller extends Controller
{

    public function series(){
        $criseriesApi = Http::get(env('base_url')."series?apikey=".env('API_KEY'));
         $seriess = json_decode($criseriesApi, true);
        $matches = $seriess['data'];
        usort($matches, function($a, $b) {
            return strtotime($a['startDate']) - strtotime($b['startDate']);
        });
        $seriess = array_slice($matches, 0, 1000);
        return view("series",compact("seriess"));
    }
    public function serieslist($id){
        $criserieslistApi = Http::get(env('base_url')."series_info?apikey=".env('API_KEY')."&id=$id");

        $serieslist = json_decode($criserieslistApi, true);
        $series = $serieslist['data']['info'];
        $matches = $serieslist['data']['matchList'];
        usort($matches, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
        $serieslists = array_slice($matches, 0, 1000);
        return view("serieslist",compact("serieslists","series"));
    }
    public function result()
    {
        //============= current match Api kay==============
        $ResultApi = Http::get(env('base_url')."cricScore?apikey=".env('API_KEY'));
        $Results = json_decode($ResultApi, true);
        $matches = $Results['data'];
        $Results = array_slice($matches, 0, 10000);
        return view("result",compact("Results"));

    }
    public function CricketliveScores()
    {
        //============= current match Api kay==============
        $LiveCricScoreApi = Http::get(env('base_url')."cricScore?apikey=".env('API_KEY'));
        $LiveCricScores = json_decode($LiveCricScoreApi, true);
        $matches = $LiveCricScores['data'];
        $LiveCricScores = array_slice($matches, 0, 10000);
        return view("index",compact("LiveCricScores"));

    }
    public function sduling(){
        $sdulingmatchsApi = Http::get(env('base_url').'cricScore?apikey='.env('API_KEY'));
        $sdulingmatchs = json_decode($sdulingmatchsApi, true);
        $matches = $sdulingmatchs['data'];
        $sdulingmatchs = array_slice($matches, 0, 10000);
        return view("sduling",compact("sdulingmatchs"));
    }

    // Match details scoreboard 

     public function matchdetail($id){
        $matchdetailApi = Http::get(env('base_url')."match_scorecard?apikey=".env('API_KEY')."&id=$id");
        $matchdetail = $matchdetailApi->json();
        $matches = $matchdetail['data']?? "Not Found Match";
        // Step 3: Filter for the 1st, 2nd, and 3rd matches

        return view("matchdetail",compact("matches"));
    }

    // ///////////////////////////////////////////////////
    
      public function index()
    {
        $response = file_get_contents('https://apiv2.allsportsapi.com/cricket/?met=Livescore&APIkey=9c6968fa36ab4b4c7f0867ff9f2c160cb9ed3f884392320439b2fea88683910d&from=12-02-2026&to=12-02-2026');
        $data = json_decode($response, true);

        $matches = $data['result']?? "not Match";

        return view('matches', compact('matches'));
    }

    // Click → full match record
    public function show($id)
    {
        $response = file_get_contents('https://apiv2.allsportsapi.com/cricket/?met=Livescore&APIkey=9c6968fa36ab4b4c7f0867ff9f2c160cb9ed3f884392320439b2fea88683910d&from=12-02-2026&to=12-02-2026');
        $data = json_decode($response, true);

        foreach ($data['result'] as $match) {
            if ($match['event_key'] == $id) {
                $matchDetails = $match;
                break;
            }
        }
        return view('match-details', compact('matchDetails'));
    }
}