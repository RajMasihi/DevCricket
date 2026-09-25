<?php

// cricbuzz_api_key = 8523f1f3d8msh9899dce10cce67bp18d09fjsn6f4dec60701d

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cricketlivescorecontroller;

// Static Pages with SEO-friendly URLs
Route::get('/about', [Cricketlivescorecontroller::class, 'about'])->name('about');
Route::get('/contact', [Cricketlivescorecontroller::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [Cricketlivescorecontroller::class, 'privacy'])->name('privacy');
Route::get('/gallery', [Cricketlivescorecontroller::class, 'gallery'])->name('gallery');

// Legacy URL redirects for static pages
Route::permanentRedirect('/privacy', '/privacy-policy');
Route::permanentRedirect('/gallary', '/gallery');

// Main Live Score Page
Route::get('/', [Cricketlivescorecontroller::class, 'CricketliveScores'])->name('home');
Route::get('/live-cricket-scores', [Cricketlivescorecontroller::class, 'CricketliveScores'])->name('live-scores');

// Match Categories with SEO-friendly URLs
Route::get('/cricket-schedule/upcoming', [Cricketlivescorecontroller::class, 'upcoming'])->name('upcoming-matches');
Route::get('/cricket-results', [Cricketlivescorecontroller::class, 'result'])->name('match-results');

// Schedule Routes with better URL structure
Route::get('/cricket-schedule/international', [Cricketlivescorecontroller::class, 'sdulinginternational'])->name('schedule-international');
Route::get('/cricket-schedule/domestic', [Cricketlivescorecontroller::class, 'sdulingdomestic'])->name('schedule-domestic');
Route::get('/cricket-schedule/womens', [Cricketlivescorecontroller::class, 'sdulingwomen'])->name('schedule-womens');
Route::get('/cricket-schedule/league', [Cricketlivescorecontroller::class, 'sdulingleague'])->name('schedule-league');

// News Routes with SEO-friendly URLs
Route::get('/cricket-news', [Cricketlivescorecontroller::class, 'newscat'])->name('news');
Route::get('/cricket-news/{id}/{slug}', [Cricketlivescorecontroller::class, 'newscatdetail'])->name('news-detail');
Route::get('/news', [Cricketlivescorecontroller::class, 'newscat'])->name('news-legacy');
Route::get('/news/{id}/{slug}', [Cricketlivescorecontroller::class, 'newscatdetail'])->name('news-detail-legacy');

// ICC Ranking Routes with better structure
Route::get('/icc-rankings/mens', [Cricketlivescorecontroller::class, 'icc_ranking'])->name('icc-rankings-mens');
Route::get('/icc-rankings/womens', [Cricketlivescorecontroller::class, 'icc_ranking'])->name('icc-rankings-womens');
Route::get('/icc-rankings/{gender}/{category?}/{format?}', [Cricketlivescorecontroller::class, 'icc_ranking'])->name('icc-rankings');
Route::get('/icc-rankings/{gender}/{category}/{format}/{id}/{slug?}', [Cricketlivescorecontroller::class, 'icc_ranking_detail'])->name('icc-rankings-detail');

// Teams Routes with better URL structure
Route::get('/cricket-teams/international', [Cricketlivescorecontroller::class, 'teamsinternational'])->name('teams-international');
Route::get('/cricket-teams/domestic', [Cricketlivescorecontroller::class, 'teamsdomestic'])->name('teams-domestic');
Route::get('/cricket-teams/womens', [Cricketlivescorecontroller::class, 'teamswomens'])->name('teams-womens');
Route::get('/cricket-teams/league', [Cricketlivescorecontroller::class, 'teamsleague'])->name('teams-league');
Route::get('/cricket-teams/{id}/{slug?}', [Cricketlivescorecontroller::class, 'teamDetail'])->name('team-detail');

// Series Routes with SEO-friendly URLs
Route::get('/cricket-series', [Cricketlivescorecontroller::class, 'series'])->name('series');
Route::get('/cricket-series/{id}/{slug}', [Cricketlivescorecontroller::class, 'serieslist'])->name('series-detail');
Route::get('/serieslist/{id}/{slug}', [Cricketlivescorecontroller::class, 'serieslist'])->name('series-detail-legacy');

// Match Scorecard Routes with better structure
Route::get('/live-cricket-score/{id}/{slug}', [Cricketlivescorecontroller::class, 'matchDetail'])->name('match-detail');
Route::get('/score/{id}/{slug}', [Cricketlivescorecontroller::class, 'matchDetail'])->name('match-detail-legacy');
Route::get('/match-scoreboard/{id}/{slug}', function (string $id, string $slug) {
    return redirect("/live-cricket-score/{$id}/{$slug}?tab=scoreboard");
})->name('match-scoreboard');
Route::get('/match-players/{id}/{slug}', function (string $id, string $slug) {
    return redirect("/live-cricket-score/{$id}/{$slug}?tab=players");
})->name('match-players');

// Point Table and Stats Routes
Route::get('/point-table/{id}/{slug}', [Cricketlivescorecontroller::class, 'showSeriesPoints'])->name('point-table');
Route::get('/series-stats/{id}/{slug}', [Cricketlivescorecontroller::class, 'stats'])->name('series-stats');
Route::get('/stats/{id}/{slug}', [Cricketlivescorecontroller::class, 'stats'])->name('stats-legacy');

// Legacy URL redirects for SEO (301 permanent redirects)
Route::permanentRedirect('/upcoming', '/cricket-schedule/upcoming');
Route::permanentRedirect('/result', '/cricket-results');
Route::permanentRedirect('/news', '/cricket-news');
Route::permanentRedirect('/sduling/international', '/cricket-schedule/international');
Route::permanentRedirect('/sduling/domestic', '/cricket-schedule/domestic');
Route::permanentRedirect('/sduling/womens', '/cricket-schedule/womens');
Route::permanentRedirect('/sduling/league', '/cricket-schedule/league');
Route::permanentRedirect('/teams/international', '/cricket-teams/international');
Route::permanentRedirect('/teams/domestic', '/cricket-teams/domestic');
Route::permanentRedirect('/teams/womens', '/cricket-teams/womens');
Route::permanentRedirect('/teams/league', '/cricket-teams/league');
Route::permanentRedirect('/series', '/cricket-series');
Route::permanentRedirect('/serieslist/{id}/{slug}', '/cricket-series/{id}/{slug}');
Route::permanentRedirect('/icc-ranking/mens', '/icc-rankings/mens');
Route::permanentRedirect('/icc-ranking/womens', '/icc-rankings/womens');
Route::permanentRedirect('/score/{id}/{name}', '/live-cricket-score/{id}/{name}');
Route::permanentRedirect('/stats/{id}/{slug}', '/series-stats/{id}/{slug}');

// Sitemap
Route::get('/sitemap.xml', [Cricketlivescorecontroller::class, 'sitemap'])->name('sitemap');

