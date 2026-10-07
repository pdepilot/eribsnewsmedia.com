@props(['article', 'variant' => 'magazine'])
@php($url = route('articles.show', $article))

@if ($variant === 'mosaic')
    <article class="tile tile-mosaic">
        <a class="tile-media" href="{{ $url }}">
            @if ($article->featuredImageUrl())
                <img src="{{ $article->featuredImageUrl() }}" alt="{{ $article->featured_image_alt ?: $article->title }}">
            @else
                <span class="tile-fallback"></span>
            @endif
        </a>
        <div class="tile-body">
            <h3><a href="{{ $url }}">{{ $article->title }}</a></h3>
            <p class="tile-meta">
                @if ($article->postedLabel())
                    <time datetime="{{ $article->published_at->toAtomString() }}">Posted {{ $article->postedLabel() }}</time>
                @endif
                <a href="{{ $url }}#comments">{{ $count = $article->comments_count ?? 0 }} comment{{ (int) $count > 1 ? 's' : '' }}</a>
            </p>
        </div>
    </article>
@elseif ($variant === 'popular')
    <article class="tile tile-popular">
        <a class="tile-media" href="{{ $url }}">
            @if ($article->featuredImageUrl())
                <img src="{{ $article->featuredImageUrl() }}" alt="{{ $article->featured_image_alt ?: $article->title }}" loading="lazy">
            @else
                <span class="tile-fallback"></span>
            @endif
        </a>
        <div class="tile-body">
            <h3><a href="{{ $url }}">{{ $article->title }}</a></h3>
            @if ($article->postedLabel())
                <time datetime="{{ $article->published_at->toAtomString() }}">Posted {{ $article->postedLabel() }}</time>
            @endif
        </div>
    </article>
@elseif ($variant === 'lead')
    <article class="lead-post">
        <a class="thumb" href="{{ $url }}">
            @if ($article->featuredImageUrl())
                <img src="{{ $article->featuredImageUrl() }}" alt="{{ $article->featured_image_alt ?: $article->title }}" loading="lazy">
            @endif
        </a>
        <h3><a href="{{ $url }}">{{ $article->title }}</a></h3>
        <p class="byline">
            @if ($article->author)
                by <a href="{{ route('authors.show', $article->author) }}">{{ $article->author->name }}</a>
            @endif
            @if ($article->postedLabel())
                <time datetime="{{ $article->published_at->toAtomString() }}">Posted {{ $article->postedLabel() }}</time>
            @endif
        </p>
        <p class="excerpt">{{ $article->summary() }}</p>
    </article>
@elseif ($variant === 'mini')
    <article class="mini-post">
        <a class="thumb" href="{{ $url }}">
            @if ($article->featuredImageUrl())
                <img src="{{ $article->featuredImageUrl() }}" alt="{{ $article->featured_image_alt ?: $article->title }}" loading="lazy">
            @endif
        </a>
        <div>
            <h3><a href="{{ $url }}">{{ $article->title }}</a></h3>
            @if ($article->postedLabel())
                <time class="quiet" datetime="{{ $article->published_at->toAtomString() }}">Posted {{ $article->postedLabel() }}</time>
            @endif
        </div>
    </article>
@else
    <article class="mag-item">
        <a class="thumb" href="{{ $url }}">
            @if ($article->featuredImageUrl())
                <img src="{{ $article->featuredImageUrl() }}" alt="{{ $article->featured_image_alt ?: $article->title }}" loading="lazy">
            @endif
        </a>
        <div>
            @if ($article->category)
                <a class="kicker" href="{{ route('categories.show', $article->category) }}">{{ $article->category->name }}</a>
            @endif
            <h2><a href="{{ $url }}">{{ $article->title }}</a></h2>
            <p class="byline">
                @if ($article->author)
                    by <a href="{{ route('authors.show', $article->author) }}">{{ $article->author->name }}</a>
                @endif
                @if ($article->postedLabel())
                    <time datetime="{{ $article->published_at->toAtomString() }}">Posted {{ $article->postedLabel() }}</time>
                @endif
            </p>
            <p class="excerpt">{{ $article->summary() }}</p>
        </div>
    </article>
@endif
