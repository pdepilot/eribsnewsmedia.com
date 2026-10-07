@php($site = app(\App\Services\SiteContext::class)->data())
<footer class="footer">
    <div class="footer-filament" aria-hidden="true"></div>
    <div class="footer-orbit" aria-hidden="true"></div>
    <div class="footer-orbit footer-orbit-inner" aria-hidden="true"></div>
    <p class="footer-word" aria-hidden="true">ERIBS</p>
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="footer-logo" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="{{ $site['siteName'] }}" width="636" height="280">
            </a>
            <p class="footer-copy">© {{ $site['siteSettings']['copyright'] ?? $site['siteName'] }}</p>
            <p class="footer-place">Lagos · {{ now()->format('l, j F Y') }}</p>
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
        <nav class="footer-index" aria-label="Footer">
            <ol>
                <li>
                    <a href="{{ route('home') }}">
                        <span>01</span>
                        Home
                    </a>
                </li>
                @foreach ($site['navCategories'] as $category)
                    <li>
                        <a href="{{ route('categories.show', $category) }}">
                            <span>{{ str_pad((string) ($loop->iteration + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
                @foreach ([
                    'pages.contact' => 'Contact Us',
                    'pages.about' => 'About Us',
                    'pages.privacy' => 'Privacy Policy',
                    'pages.terms' => 'Terms of Use',
                ] as $name => $label)
                    <li>
                        <a href="{{ route($name) }}">
                            <span>{{ str_pad((string) ($site['navCategories']->count() + 1 + $loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>
        <div class="footer-close">
            <a class="footer-rise" href="#top">
                <span aria-hidden="true">↑</span>
                Back to the top
            </a>
            <a class="footer-rss" href="{{ route('feeds.rss') }}">The wire · RSS</a>
        </div>
    </div>
</footer>
