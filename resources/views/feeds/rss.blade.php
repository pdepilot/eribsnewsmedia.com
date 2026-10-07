{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0">
<channel>
    <title>{{ $siteName }}</title>
    <link>{{ url('/') }}</link>
    <description>{{ $description }}</description>
    <language>en-ng</language>
    <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
    @foreach ($articles as $article)
        <item>
            <title>{{ $article->title }}</title>
            <link>{{ route('articles.show', $article) }}</link>
            <guid>{{ route('articles.show', $article) }}</guid>
            <pubDate>{{ $article->published_at?->toRssString() }}</pubDate>
            @if ($article->author?->email)
                <author>{{ $article->author->email }} ({{ $article->author->name }})</author>
            @endif
            @if ($article->category)
                <category>{{ $article->category->name }}</category>
            @endif
            <description>{{ $article->summary() }}</description>
        </item>
    @endforeach
</channel>
</rss>
