@props(['except' => []])
@php
    $desk = app(\App\Services\SiteContext::class)->data();
    $skip = collect($except)->map(fn ($id) => (int) $id)->all();
    $recent = $desk['recentPosts']
        ->reject(fn ($article) => in_array($article->id, $skip, true))
        ->values();
    $comments = $desk['recentComments'];
    $advertising = app(\App\Services\Advertising\AdvertisingService::class);
    $hasAd = collect(['sidebar', 'sidebar-top', 'sidebar-middle', 'sidebar-bottom'])->contains(
        fn (string $name) => $advertising->resolve($name) !== null
    );
@endphp
@if ($recent->isNotEmpty() || $comments->isNotEmpty() || $hasAd)
    <aside class="sidebar">
        @if ($recent->isNotEmpty())
            <section class="widget">
                <h2 class="mag-title"><span>Recent Posts</span></h2>
                <ul class="recent">
                    @foreach ($recent as $article)
                        <li>
                            <a href="{{ route('articles.show', $article) }}">{{ $article->title }}</a>
                            @if ($article->postedLabel())
                                <time class="quiet" datetime="{{ $article->published_at->toAtomString() }}">Posted {{ $article->postedLabel() }}</time>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
        @if ($comments->isNotEmpty())
            <section class="widget">
                <h2 class="mag-title"><span>Recent Comments</span></h2>
                <ul class="recent">
                    @foreach ($comments as $comment)
                        <li>
                            <span class="quiet">{{ $comment->name }} on</span>
                            @if ($comment->article)
                                <a href="{{ route('articles.show', $comment->article) }}#comments">{{ $comment->article->title }}</a>
                            @else
                                a removed story
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
        <x-ad-slot name="sidebar-top" />
        <x-ad placement="sidebar" />
        <x-ad-slot name="sidebar-middle" />
        <x-ad-slot name="sidebar-bottom" />
    </aside>
@endif
