@extends('layouts.main')

@section('title', 'Cricket News - Latest Updates | Criclivem')

@section('meta-description')
    <meta name="description" content="Get the latest cricket news, match updates, player interviews, and cricket analysis from around the world. Stay informed with breaking cricket news and in-depth coverage.">
@endsection

@section('meta-keywords')
    <meta name="keywords" content="cricket news, latest cricket updates, cricket interviews, cricket analysis, breaking cricket news, cricket headlines, sports news">
@endsection

@section('main-container')
    

    <div class="container-fluid main-section">
        <h4 class="text-center mb-3">Latest News</h4>

        <!-- Category Horizontal Scroll Container -->
        <div class="category-scroll-wrapper mb-3">
            <div class="category-nav-container pb-2 gap-3">
                @if(isset($categories) && count($categories) > 0)
                    @foreach($categories as $category)
                        @php
                            $catId = $category['id'] ?? '';
                            $catName = $category['name'] ?? 'News';
                            $hiddenCatIds = [2, 3, 5, 8, 13, 20];
                        @endphp

                        {{-- Skip hidden category IDs --}}
                        @continue(in_array((int)$catId, $hiddenCatIds))

                        <a href="{{ url('/cricket-news?cat=' . $catId) }}"
                           class="btn scoreboard-title {{ (string)$activeCategoryId === (string)$catId ? 'active-news-tab' : '' }}">
                            {{ $catName }}
                        </a>
                    @endforeach
                @else
                    <span>No categories available.</span>
                @endif
            </div>
        </div>

        @if(isset($errorMsg))
            <p class="text-danger">{{ $errorMsg }}</p>
        @endif

        <div class="row row-cols-1 row-cols-md-2 g-4 pt-2">
            @if(isset($newsItems) && count($newsItems) > 0)
                @foreach($newsItems as $storyWrap)
                    @php
                        $story = $storyWrap['story'] ?? [];
                        $storyId = $story['id'] ?? '';
                        $headline = $story['hline'] ?? 'No title';
                        $intro = $story['intro'] ?? '';
                        $source = $story['source'] ?? '';
                        $publishedTime = !empty($story['pubTime']) ? date('d M Y, h:i A', ((int)$story['pubTime']) / 1000) : '';
                        $imageId = $story['imageId'] ?? '';
                        $imageUrl = !empty($imageId) ? "https://static.cricbuzz.com/a/img/v1/500x300/i1/c{$imageId}/news.jpg" : '';
                        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $headline), '-'));
                    @endphp

                    @if(!empty($storyId))
                        <div class="col">
                            <a href="{{ url('/cricket-news/' . $storyId . '/' . $slug) }}" style="text-decoration:none; color:#141010;">
                                <div class="card h-80" style="box-shadow: 2px 2px 6px 1px #053259;">
                                    @if(!empty($imageUrl))
                                        <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $headline }}">
                                    @endif
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $headline }}</h5>
                                        @if(!empty($intro))
                                            <p class="card-text">{{ $intro }}</p>
                                        @endif
                                        {{-- Match Time/Status Display --}}
                                        @if(isset($story['matchTime']) && !empty($story['matchTime']))
                                            <div class="mt-2 pt-2 border-top">
                                                @php
                                                    $matchState = $story['matchState'] ?? '';
                                                    $matchStatus = $story['matchStatus'] ?? '';
                                                @endphp
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted" style="font-size: 12px;">
                                                        <i class="far fa-clock"></i> {{ $story['matchTime'] }}
                                                    </span>
                                                    @if($matchState === 'in progress')
                                                        <span class="badge bg-success" style="font-size: 10px;">Live</span>
                                                    @elseif($matchState === 'complete')
                                                        <span class="badge bg-danger" style="font-size: 10px;">Result</span>
                                                    @elseif($matchState === 'upcoming')
                                                        <span class="badge bg-secondary" style="font-size: 10px;">Upcoming</span>
                                                    @endif
                                                </div>
                                                @if(!empty($matchStatus))
                                                    <p class="text-muted mb-0" style="font-size: 11px;">{{ $matchStatus }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-footer d-flex justify-content-between">
                                        <small class="text-muted">{{ $publishedTime }}</small>
                                        @if(!empty($source))
                                            <small class="text-muted">{{ $source }}</small>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            @else
                <p>No news found for this category.</p>
            @endif
        </div>
    </div>
@endsection