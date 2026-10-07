@extends('layouts.public')

@section('meta')
    <x-seo :article="$article" />
@endsection

@section('content')
    <div class="container magazine">
        <article class="page-block">
            <p class="crumbs quiet">
                <a href="{{ route('home') }}">Home</a>
                @if ($article->category)
                    · <a href="{{ route('categories.show', $article->category) }}">{{ $article->category->name }}</a>
                @endif
            </p>
            @if ($article->category)
                <a class="kicker article-kicker" href="{{ route('categories.show', $article->category) }}">{{ $article->category->name }}</a>
            @endif
            <h1 class="article-title">{{ $article->title }}</h1>
            <p class="byline">
                @if ($article->author)
                    by <a href="{{ route('authors.show', $article->author) }}">{{ $article->author->name }}</a>
                @endif
                @if ($article->postedLabel())
                    <time datetime="{{ $article->published_at->toAtomString() }}">Posted {{ $article->postedLabel() }}</time>
                @endif
                @if ($article->updated_at && $article->published_at && $article->updated_at->gt($article->published_at))
                    · Updated <time datetime="{{ $article->updated_at->toAtomString() }}">{{ $article->updated_at->format('F j, Y g:i A') }}</time>
                @endif
            </p>
            @if ($article->featuredImageUrl())
                <figure class="article-figure">
                    <img src="{{ $article->featuredImageUrl() }}" alt="{{ $article->featured_image_alt ?: $article->title }}">
                    @if ($article->featuredMedia?->credit || $article->featuredMedia?->caption)
                        <figcaption>{{ $article->featuredMedia?->caption }} {{ $article->featuredMedia?->credit }}</figcaption>
                    @endif
                </figure>
            @endif
            <x-ad-slot name="article-after-intro" />
            <x-ad placement="article_top" />
            <div class="article-body">{!! nl2br(e($article->body)) !!}</div>
            <x-ad placement="article_middle" />
            @if ($article->tags->isNotEmpty())
                <div class="tags">
                    @foreach ($article->tags as $tag)
                        <span class="tag">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
            <div class="share">
                <a href="https://twitter.com/intent/tweet?url={{ urlencode($article->canonicalUrl()) }}&text={{ urlencode($article->title) }}" rel="noopener" target="_blank">Share on X</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($article->canonicalUrl()) }}" rel="noopener" target="_blank">Share on Facebook</a>
            </div>
            <x-ad placement="article_bottom" />
            <section id="comments" class="comments">
                <h2 class="mag-title"><span>{{ $article->comments_count }} comment{{ (int) $article->comments_count > 1 ? 's' : '' }}</span></h2>
                @if ($article->comments->isNotEmpty())
                    <ol class="comment-list">
                        @foreach ($article->comments as $comment)
                            <li>
                                <strong>{{ $comment->name }}</strong>
                                <time datetime="{{ $comment->created_at->toAtomString() }}">{{ $comment->created_at->format('F j, Y') }}</time>
                                <p>{{ $comment->body }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
                <h3>Leave a comment</h3>
                <form class="comment-form" method="post" action="{{ route('comments.store', $article) }}">
                    @csrf
                    <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <label>Name <input name="name" value="{{ old('name') }}" required></label>
                    <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label>
                    <label>Comment <textarea name="body" rows="5" required>{{ old('body') }}</textarea></label>
                    <button class="btn" type="submit">Post Comment</button>
                </form>
            </section>
            @if ($related->isNotEmpty())
                <section class="related">
                    <h2 class="mag-title"><span>More {{ $article->category?->name ?: 'stories' }}</span></h2>
                    <div class="related-row">
                        @foreach ($related as $item)
                            <x-story :article="$item" variant="popular" />
                        @endforeach
                    </div>
                </section>
            @endif
        </article>
        <x-site-sidebar :except="collect([$article->id])->merge($related->pluck('id'))" />
    </div>
@endsection
