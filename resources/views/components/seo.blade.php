@props(['article' => null, 'title' => null, 'description' => null, 'canonical' => null])
@php
    $site = app(\App\Services\SiteContext::class)->data();
    $settings = $site['siteSettings'];
    $siteName = $site['siteName'];
    $pageTitle = $article ? $article->seoTitle() : ($title ?: $siteName);
    $fullTitle = $pageTitle === $siteName ? $siteName : trim($pageTitle.' | '.$siteName);
    $pageDescription = $article ? $article->seoDescription() : ($description ?: ($settings['seo_default_description'] ?? ''));
    $pageCanonical = $article ? $article->canonicalUrl() : ($canonical ?: url()->current());
    $image = $article?->openGraphImage();
    $publisher = $settings['publisher_name'] ?? $siteName;
    $schemaType = $article && $article->is_opinion ? 'OpinionNewsArticle' : 'NewsArticle';
    $schema = $article ? [
        '@context' => 'https://schema.org',
        '@type' => $schemaType,
        'headline' => $article->title,
        'datePublished' => optional($article->published_at)->toAtomString(),
        'dateModified' => optional($article->updated_at)->toAtomString(),
        'description' => $pageDescription,
        'mainEntityOfPage' => $pageCanonical,
        'author' => [
            '@type' => 'Person',
            'name' => $article->author?->name ?: $publisher,
        ],
        'publisher' => array_filter([
            '@type' => 'Organization',
            'name' => $publisher,
            'logo' => ($publisherLogo = app(\App\Services\Settings::class)->logoUrl($settings['publisher_logo'] ?? null)) ? [
                '@type' => 'ImageObject',
                'url' => $publisherLogo,
            ] : null,
        ]),
        'image' => $image ? [$image] : null,
    ] : [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => route('search').'?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
<title>{{ $fullTitle }}</title>
@if (filled($pageDescription))
    <meta name="description" content="{{ $pageDescription }}">
@endif
<link rel="canonical" href="{{ $pageCanonical }}">
<link rel="alternate" type="application/rss+xml" title="{{ $siteName }} RSS" href="{{ route('feeds.rss') }}">
<meta property="og:locale" content="en_NG">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $article ? 'article' : 'website' }}">
<meta property="og:title" content="{{ $article?->og_title ?: $pageTitle }}">
<meta property="og:url" content="{{ $pageCanonical }}">
@if (filled($article?->og_description ?: $pageDescription))
    <meta property="og:description" content="{{ $article?->og_description ?: $pageDescription }}">
@endif
@if ($image)
    <meta property="og:image" content="{{ $image }}">
@endif
@if ($article?->published_at)
    <meta property="article:published_time" content="{{ $article->published_at->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at->toAtomString() }}">
@endif
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $article?->og_title ?: $pageTitle }}">
@if (filled($article?->og_description ?: $pageDescription))
    <meta name="twitter:description" content="{{ $article?->og_description ?: $pageDescription }}">
@endif
@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif
@if (filled($settings['twitter_handle'] ?? null))
    <meta name="twitter:site" content="{{ $settings['twitter_handle'] }}">
@endif
@if (($settings['analytics_enabled'] ?? '0') === '1' && filled($settings['analytics_id'] ?? null))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings['analytics_id'] }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json($settings['analytics_id']));
    </script>
@endif
<script type="application/ld+json">{!! json_encode(array_filter($schema), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
