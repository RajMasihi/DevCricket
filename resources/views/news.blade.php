@extends('layouts.main')

@section('title', 'Cricket News')

@section('main-container')
    <style>
        .active-news-tab {
            border-bottom: 2px solid #053259 !important;
        }
    </style>

    <div class="container-fluid main-section">
        <h4 class="text-center mb-3">Latest Cricket News</h4>

        <div class="d-flex flex-wrap pb-3 gap-2">
            @if(isset($categories) && count($categories) > 0)
                @foreach($categories as $category)
                    @php
                        $catId = $category['id'] ?? '';
                        $catName = $category['name'] ?? 'News';
                    @endphp
                    <a href="{{ url('/news?cat=' . $catId) }}"
                       class="btn scoreboard-title {{ (string)$activeCategoryId === (string)$catId ? 'active-news-tab' : '' }}">
                        {{ $catName }}
                    </a>
                @endforeach
            @else
                <span>No categories available.</span>
            @endif
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
                            <a href="{{ url('/news/' . $storyId . '/' . $slug) }}" style="text-decoration:none; color:#141010;">
                                <div class="card h-80" style="box-shadow: 2px 2px 6px 1px #053259;">
                                    {{-- show image only when image exists --}}
                                    @if(!empty($imageUrl))
                                        <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $headline }}">
                                    @endif
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $headline }}</h5>
                                        @if(!empty($intro))
                                            <p class="card-text">{{ $intro }}</p>
                                        @endif
                                    </div>
                                    <div class="card-footer d-flex justify-content-between">
                                        <!-- <small class="text-muted">{{ $source }}</small> -->
                                        <small class="text-muted">{{ $publishedTime }}</small>
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