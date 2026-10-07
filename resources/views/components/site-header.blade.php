@php($site = app(\App\Services\SiteContext::class)->data())
@php($count = $site['breakingItems']->count())
<header class="masthead">
    <div class="mast-filament" aria-hidden="true"></div>
    <div class="container mast-top">
        <p class="mast-meta">
            <time datetime="{{ now()->toDateString() }}">{{ now()->format('l, j F Y') }}</time>
            <span>Lagos</span>
        </p>
        <div class="socials">
            @foreach ([
                'social_facebook' => 'Facebook',
                'social_x' => 'X',
                'social_instagram' => 'Instagram',
                'social_pinterest' => 'Pinterest',
                'social_youtube' => 'YouTube',
            ] as $key => $label)
                <a href="{{ filled($site['siteSettings'][$key] ?? null) ? $site['siteSettings'][$key] : '#' }}" aria-label="{{ $label }}" @if(filled($site['siteSettings'][$key] ?? null)) rel="noopener noreferrer" target="_blank" @endif>
                    @include('components.social-icon', ['name' => $key])
                </a>
            @endforeach
            <a href="{{ filled($site['siteSettings']['contact_email'] ?? null) ? 'mailto:'.$site['siteSettings']['contact_email'] : '#' }}" aria-label="Email">
                @include('components.social-icon', ['name' => 'email'])
            </a>
            <a href="{{ route('feeds.rss') }}" aria-label="RSS">
                @include('components.social-icon', ['name' => 'rss'])
            </a>
        </div>
    </div>

    <div class="container mast-brand">
        <a class="logo" href="{{ route('home') }}">
            <img class="brand-logo" src="{{ asset('images/logo.png').'?v=2' }}" alt="{{ $site['siteName'] }}" width="652" height="263">
        </a>
        <div class="wire" data-breaking-wire>
            <span class="wire-ghost" @if($count === 0) hidden @endif data-breaking-ghost>{{ $count > 0 ? '01' : '' }}</span>
            <div class="wire-kicker">
                <span class="live-dot" aria-hidden="true"></span>
                <span>Breaking News</span>
                @if ($count > 1)
                    <span class="wire-count"><span data-breaking-pos>01</span><span> / {{ str_pad((string) $count, 2, '0', STR_PAD_LEFT) }}</span></span>
                @endif
            </div>
            <div class="ticker-track">
                @forelse ($site['breakingItems'] as $index => $item)
                    @if (filled($item->url))
                        <a data-breaking-item @class(['is-on' => $index === 0]) href="{{ $item->url }}">{{ $item->title }}</a>
                    @else
                        <span data-breaking-item @class(['is-on' => $index === 0])>{{ $item->title }}</span>
                    @endif
                @empty
                    <span class="ticker-empty">No active breaking news</span>
                @endforelse
            </div>
            @if ($count > 1)
                <div class="ticker-nav">
                    <button type="button" data-breaking-prev aria-label="Previous headline">←</button>
                    <button type="button" data-breaking-next aria-label="Next headline">→</button>
                </div>
            @endif
        </div>
        <script>
            document.querySelectorAll('[data-breaking-wire]').forEach(function (wire) {
                if (wire.dataset.breakingReady === '1') return;
                wire.dataset.breakingReady = '1';
                var items = Array.prototype.slice.call(wire.querySelectorAll('[data-breaking-item]'));
                if (items.length < 2) return;
                var index = 0;
                var ghost = wire.querySelector('[data-breaking-ghost]');
                var pos = wire.querySelector('[data-breaking-pos]');
                var show = function (next) {
                    index = (next + items.length) % items.length;
                    items.forEach(function (el, itemIndex) {
                        el.classList.toggle('is-on', itemIndex === index);
                    });
                    var label = String(index + 1).padStart(2, '0');
                    if (ghost) ghost.textContent = label;
                    if (pos) pos.textContent = label;
                };
                var prev = wire.querySelector('[data-breaking-prev]');
                var next = wire.querySelector('[data-breaking-next]');
                if (prev) prev.addEventListener('click', function () { show(index - 1); });
                if (next) next.addEventListener('click', function () { show(index + 1); });
                if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    window.setInterval(function () { show(index + 1); }, 5000);
                }
            });
        </script>
    </div>

    <nav class="nav-bar" aria-label="Primary">
        <div class="container nav-row">
            <button type="button" class="menu-toggle" @click="navOpen = !navOpen" :aria-expanded="navOpen.toString()" aria-label="Open menu">
                <svg width="18" height="14" viewBox="0 0 18 14" fill="currentColor" aria-hidden="true"><rect width="18" height="2"/><rect y="6" width="18" height="2"/><rect y="12" width="18" height="2"/></svg>
            </button>
            <ul class="nav-menu" :class="{ 'is-open': navOpen }">
                <li class="drawer-logo"><a href="{{ route('home') }}" @click="navOpen = false"><img src="{{ asset('images/logo.png').'?v=2' }}" alt="{{ $site['siteName'] }}"></a></li>
                <li><a class="nav-home @if(request()->routeIs('home')) is-current @endif" href="{{ route('home') }}" @click="navOpen = false">Home</a></li>
                @foreach ($site['navCategories'] as $category)
                    <li><a class="@if(request()->routeIs('categories.show') && request()->route('category')?->is($category)) is-current @endif" href="{{ route('categories.show', $category) }}">{{ $category->name }}</a></li>
                @endforeach
                <li><a class="@if(request()->routeIs('pages.contact')) is-current @endif" href="{{ route('pages.contact') }}">Contact Us</a></li>
                <li><a class="@if(request()->routeIs('pages.about')) is-current @endif" href="{{ route('pages.about') }}">About Us</a></li>
                <li><a class="@if(request()->routeIs('pages.privacy')) is-current @endif" href="{{ route('pages.privacy') }}">Privacy Policy</a></li>
                <li><a class="@if(request()->routeIs('pages.terms')) is-current @endif" href="{{ route('pages.terms') }}">Terms of Use</a></li>
                <li class="drawer-close"><button type="button" @click="navOpen = false">Close</button></li>
            </ul>
            <form class="search-pill" action="{{ route('search') }}" method="get" :class="{ 'is-open': searchOpen }" @click.outside="searchOpen = false">
                <input type="search" name="q" placeholder="Search stories" aria-label="Search" :tabindex="searchOpen ? 0 : -1">
                <button type="button" class="icon-btn" @click="searchOpen = !searchOpen; if (searchOpen) $nextTick(() => $el.previousElementSibling.focus())" :aria-expanded="searchOpen.toString()" aria-label="Search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                </button>
            </form>
        </div>
    </nav>
</header>
<div class="nav-backdrop" :class="{ 'is-open': navOpen }" @click="navOpen = false"></div>
