@props(['name'])
@php($fill = app(\App\Services\Advertising\AdvertisingService::class)->resolve($name))
@if ($fill)
    <aside class="ad-slot ad-slot-{{ str_replace('-', '_', $name) }} ad-only-{{ $fill->device }}" aria-label="{{ $fill->label }}" @if($fill->source === 'direct_campaign') data-ad-seen="{{ route('advertising.impression', $fill->campaign) }}" @endif>
        <p class="ad-label">{{ $fill->label }}</p>
        @if ($fill->source === 'direct_campaign' && $fill->campaign?->creative)
            @php($creative = $fill->campaign->creative)
            @if ($creative->type === 'image' && $creative->imageUrl())
                @if (app(\App\Services\Advertising\AdvertisingService::class)->safeHttpUrl($fill->campaign->click_url))
                    <a href="{{ route('advertising.click', $fill->campaign) }}" rel="sponsored nofollow noopener" target="_blank">
                        <img src="{{ $creative->imageUrl() }}" alt="{{ $creative->alt_text ?: $fill->campaign->name }}" width="{{ $creative->width }}" height="{{ $creative->height }}" loading="lazy">
                    </a>
                @else
                    <img src="{{ $creative->imageUrl() }}" alt="{{ $creative->alt_text ?: $fill->campaign->name }}" width="{{ $creative->width }}" height="{{ $creative->height }}" loading="lazy">
                @endif
            @elseif (filled($creative->html))
                {!! $creative->html !!}
            @endif
        @elseif ($fill->source === 'legacy' && $fill->legacy)
            @if ($fill->legacy->type === 'image' && $fill->legacy->imageUrl())
                @if (app(\App\Services\Advertising\AdvertisingService::class)->safeHttpUrl($fill->legacy->target_url))
                    <a href="{{ $fill->legacy->target_url }}" rel="sponsored nofollow noopener" target="_blank">
                        <img src="{{ $fill->legacy->imageUrl() }}" alt="{{ $fill->legacy->alt_text ?: $fill->legacy->name }}" loading="lazy">
                    </a>
                @else
                    <img src="{{ $fill->legacy->imageUrl() }}" alt="{{ $fill->legacy->alt_text ?: $fill->legacy->name }}" loading="lazy">
                @endif
            @elseif (filled($fill->legacy->code))
                {!! $fill->legacy->code !!}
            @endif
        @elseif ($fill->source === 'third_party' && filled($fill->unit?->markup))
            {!! $fill->unit->markup !!}
        @elseif ($fill->source === 'adsense')
            {!! app(\App\Services\Advertising\AdvertisingService::class)->adsenseMarkup($fill->unit) !!}
        @endif
    </aside>
@endif
