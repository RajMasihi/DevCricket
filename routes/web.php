<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cricketlivescorecontroller;
// Route::get('/', function () {
//     return view('index');
// });

Route::view('/about', 'about');
Route::view('/contact', 'contact');
Route::view('/privacy', 'privacy');
Route::view('/gallary', 'gallary');
Route::view('/news', 'news');
// Route::view('/sduling', 'sduling');
// Route::view('/series', 'series');
// Route::view('/result', 'result');

// Route::get('/live',[Cricketlivescorecontroller::class,'Cricketlive']);

Route::get('/',[Cricketlivescorecontroller::class,'CricketliveScores']);
Route::get('/series',[Cricketlivescorecontroller::class,'series']);
Route::get('/result', [Cricketlivescorecontroller::class,'result']);
Route::get('/sduling',[Cricketlivescorecontroller::class,'sduling']);
Route::get('/serieslist/{id}',[Cricketlivescorecontroller::class,'serieslist']);
Route::get('/score/{id}',[Cricketlivescorecontroller::class,'matchdetail']);
// Route::get('/score',[Cricketlivescorecontroller::class,'matchdetail']);

Route::get('/matches', [Cricketlivescorecontroller::class, 'index'])->name('matches.list');
Route::get('/match/{id}', [Cricketlivescorecontroller::class, 'show'])->name('match.details');
