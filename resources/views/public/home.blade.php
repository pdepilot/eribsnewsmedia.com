@extends('layouts.public')

@section('meta')
    <x-seo :canonical="route('home')" />
@endsection

@section('content')
    <div class="container ad-row">
        <x-ad placement="header" />
    </div>

    <section class="featured">
        <div class="container">
            @if ($slides->isNotEmpty())
                <div class="featured-slider" x-data="{ i: 0, n: {{ $slides->count() }} }" x-init="if (n > 1) setInterval(() => i = (i + 1) % n, 4000)">
                    @foreach ($slides as $index => $group)
                        <div class="mosaic" x-show="i === {{ $index }}" @if($index > 0) x-cloak @endif>
                            @foreach ($group as $article)
                                <x-story :article="$article" variant="mosaic" />
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mosaic" aria-hidden="true">
                    @for ($tile = 0; $tile < 6; $tile++)
                        <div class="tile tile-empty"></div>
                    @endfor
                </div>
            @endif
        </div>
    </section>

    <div class="container ad-row">
        <x-ad-slot placement="homepage-after-featured" />
        <x-ad-slot name="homepage-after-hero" />
        <x-ad placement="homepage_top" />
    </div>

    <section class="container popular">
        <h2 class="popular-title"><span>Popular Posts</span></h2>
        @if ($popular->isNotEmpty())
            <div class="popular-row">
                @foreach ($popular as $article)
                    <x-story :article="$article" variant="popular" />
                @endforeach
            </div>
        @else
            <div class="empty">Stories with readers will be listed here.</div>
        @endif
    </section>

    <div class="container">
        <x-ad placement="homepage_middle" />
    </div>

    <div class="container magazine">
        <div>
            @foreach ($sections as $section)
                <section class="home-cat">
                    <h2 class="mag-title"><a href="{{ route('categories.show', $section['category']) }}">{{ $section['category']->name }}</a></h2>
                    @if ($section['articles']->isNotEmpty())
                        <div class="cat-block">
                            <x-story :article="$section['articles']->first()" variant="lead" />
                            <div class="cat-side">
                                @foreach ($section['articles']->skip(1) as $article)
                                    <x-story :article="$article" variant="mini" />
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty">No {{ $section['category']->name }} stories published yet.</div>
                    @endif
                </section>
            @endforeach

            <section class="home-cat">
                <h2 class="mag-title"><span>Latest Updates</span></h2>
                @if ($latest->isNotEmpty())
                    <ul class="mag-list">
                        @foreach ($latest as $article)
                            <li><x-story :article="$article" variant="magazine" /></li>
                        @endforeach
                    </ul>
                    <p class="load-more"><a href="{{ route('articles.latest') }}">Load More Posts</a></p>
                @else
                    <div class="empty">Latest updates will show after the first article is published.</div>
                @endif
            </section>
        </div>
        <x-site-sidebar :except="$latest->pluck('id')->merge($popular->pluck('id'))->merge($slides->flatten(1)->pluck('id'))->merge($sections->flatMap(fn ($section) => $section['articles']->pluck('id')))" />
    </div>
    <div class="container">
        <x-ad-slot name="homepage-bottom" />
    </div>
@endsection
