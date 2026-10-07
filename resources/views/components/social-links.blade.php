@php($site = $site ?? app(\App\Services\SiteContext::class)->data())
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
