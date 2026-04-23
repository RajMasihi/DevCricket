@extends('layouts.main')

@section('title', 'News Details')

@section('main-container')
    <div class="container-fluid main-section">
        @php
            $story = $newsDetails ?? [];

            // Headline, publish time, intro
            $headline = $story['headline'] ?? ($story['hline'] ?? 'News Details');
            $publishedTime = !empty($story['publishTime'])
                ? date('d M Y, h:i A', ((int)$story['publishTime']) / 1000)
                : (!empty($story['pubTime']) ? date('d M Y, h:i A', ((int)$story['pubTime']) / 1000) : '');

            $intro = $story['intro'] ?? '';
            
            // Image: accept id or fallback to imageId
            $coverImage = $story['coverImage']['id'] 
                ?? ($story['coverImage']['source'] 
                ?? ($story['imageId'] ?? ''));
            $imageUrl = !empty($coverImage) ? "https://static.cricbuzz.com/a/img/v1/900x500/i1/c{$coverImage}/{$headline}.jpg" : '';

            // Article content (text blocks only)
            $contentBlocks = [];
            if (isset($story['content']) && is_array($story['content'])) {
                foreach ($story['content'] as $block) {
                    if (isset($block['content']['contentType']) && $block['content']['contentType'] === 'text') {
                        $value = $block['content']['contentValue'] ?? '';
                        if (is_string($value) && trim($value) !== '') {
                            $contentBlocks[] = $value;
                        }
                    } elseif (isset($block['contentValue']) && is_string($block['contentValue'])) {
                        $contentBlocks[] = $block['contentValue'];
                    }
                }
            }

            // Authors
            $authors = [];
            if (!empty($story['authors']) && is_array($story['authors'])) {
                foreach ($story['authors'] as $author) {
                    $name = $author['name'] ?? '';
                    $twitter = $author['twitterHandle'] ?? '';
                    $authors[] = trim($name . ($twitter ? " ($twitter)" : ''));
                }
            }

            // Tags
            $tags = [];
            if (!empty($story['tags']) && is_array($story['tags'])) {
                foreach ($story['tags'] as $tag) {
                    if (!empty($tag['itemName'])) {
                        $tags[] = $tag['itemName'];
                    }
                }
            }
        @endphp

        @if(isset($errorMsg))
            <p class="text-danger">{{ $errorMsg }}</p>
        @endif

        <div class="mb-3">
            <a href="{{ url('/news') }}" class="btn scoreboard-title">Back To News</a>
        </div>

        <div class="card" style="box-shadow: 2px 2px 6px 1px #053259;">
            {{-- Show image if exists --}}
            @if(!empty($imageUrl))
                <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $headline }}">
            @endif

            <div class="card-body">
                <h2 class="card-title">{{ $headline }}</h2>
                <div class="d-flex justify-content-between mb-2 flex-wrap">
                    <div>
                        @if(!empty($authors))
                            <small class="text-muted">By {{ implode(', ', $authors) }}</small>
                        @endif
                    </div>
                    <div>
                        <small class="text-muted">{{ $publishedTime }}</small>
                    </div>
                </div>

                @if(!empty($intro))
                    <p><strong>{{ $intro }}</strong></p>
                @endif

                @if(count($contentBlocks) > 0)
                    @foreach($contentBlocks as $block)
                        <p>{!! nl2br(e($block)) !!}</p>
                    @endforeach
                @else
                    <p>No detail content available.</p>
                @endif

                @if(!empty($tags))
                    <div class="pt-2">
                        <span class="badge bg-secondary">Tags:</span>
                        @foreach($tags as $tag)
                            <span class="badge bg-light text-dark border" style="margin-right:4px;">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
