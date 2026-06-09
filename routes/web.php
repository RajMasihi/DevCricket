<?php

// cricbuzz_api_key = 8523f1f3d8msh9899dce10cce67bp18d09fjsn6f4dec60701d

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cricketlivescorecontroller;
// Route::get('/', function () {
//     return view('index');
// });

Route::view('/about', 'about');
Route::view('/contact', 'contact');
Route::view('/privacy', 'privacy');
Route::view('/gallary', 'gallary');
// Route::view('/news-odi', 'news');
// Route::view('/news-t20', 'news');
// Route::view('/test', 'test');
// Route::view('/sduling', 'sduling');
// Route::view('/series', 'series');
// Route::view('/result', 'result');
// Route::get('/match', [Cricketlivescorecontroller::class, 'showLiveMatches']);

// Route::get('/live',[Cricketlivescorecontroller::class,'Cricketlive']);

//main line 
Route::get('/',[Cricketlivescorecontroller::class,'CricketliveScores']);
Route::get('/upcoming',[Cricketlivescorecontroller::class,'upcoming']);
Route::get('/result', [Cricketlivescorecontroller::class,'result']);
// Sduling match Route
Route::get('/sduling/international',[Cricketlivescorecontroller::class,'sdulinginternational']);
Route::get('/sduling/domestic',[Cricketlivescorecontroller::class,'sdulingdomestic']);
Route::get('/sduling/womens',[Cricketlivescorecontroller::class,'sdulingwomen']);
Route::get('/sduling/league',[Cricketlivescorecontroller::class,'sdulingleague']);
// news
Route::get('/news',[Cricketlivescorecontroller::class,'newscat']);
Route::get('/news/{id}/{name}',[Cricketlivescorecontroller::class,'newscatdetail']);
// icc ranking
Route::get('/icc-ranking/mens',[Cricketlivescorecontroller::class,'icc_ranking']);
Route::get('/icc-ranking/womens',[Cricketlivescorecontroller::class,'icc_ranking']);
Route::get('/icc-ranking/{gender}/{category?}/{format?}', [Cricketlivescorecontroller::class, 'icc_ranking']);
Route::get('/icc-ranking/{gender}/{category}/{format}/{id}/{name?}', [Cricketlivescorecontroller::class, 'icc_ranking_detail']);
// teams Route
Route::get('/teams/international',[Cricketlivescorecontroller::class,'teamsinternational']);
Route::get('/teams/domestic',[Cricketlivescorecontroller::class,'teamsdomestic']);
Route::get('/teams/womens',[Cricketlivescorecontroller::class,'teamswomens']);
Route::get('/teams/league',[Cricketlivescorecontroller::class,'teamsleague']);
// series Route
Route::get('/series',[Cricketlivescorecontroller::class,'series']);
Route::get('/serieslist/{id}/{seriesname}', [Cricketlivescorecontroller::class, 'serieslist']);
// scoreboard 
Route::get('/score/{id}/{name}', [Cricketlivescorecontroller::class, 'matchdetailinforme']);
Route::get('/score-scoreboard/{id}/{name}', [Cricketlivescorecontroller::class, 'matchdetailscore']);
Route::get('/score-player/{id}/{name}', [Cricketlivescorecontroller::class, 'matchdetailplayer']);
// point table
Route::get('/point-table/{id}/{seriesname}',[Cricketlivescorecontroller::class,'showSeriesPoints']);
Route::get('/stats/{id}/{seriesname}',[Cricketlivescorecontroller::class,'stats']);

