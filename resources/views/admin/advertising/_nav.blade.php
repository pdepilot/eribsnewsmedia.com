<nav class="mb-4 flex flex-wrap gap-2 text-sm" aria-label="Advertising">
    @foreach ([
        'admin.advertising.index' => 'Overview',
        'admin.advertising.networks.index' => 'Networks',
        'admin.advertising.placements.index' => 'Placements',
        'admin.advertising.units.index' => 'Ad units',
        'admin.advertising.advertisers.index' => 'Advertisers',
        'admin.advertising.campaigns.index' => 'Campaigns',
        'admin.advertising.creatives.index' => 'Creatives',
        'admin.advertising.ads-txt' => 'ads.txt',
        'admin.advertising.settings' => 'Settings',
    ] as $route => $label)
        <a href="{{ route($route) }}" class="rounded px-3 py-1 {{ request()->routeIs($route) || ($route !== 'admin.advertising.index' && request()->routeIs(str_replace('.index', '.*', $route))) ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-700' }}">{{ $label }}</a>
    @endforeach
</nav>
@if ($errors->any())
    <div class="mb-4 rounded bg-red-50 px-3 py-2 text-sm text-red-800">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
