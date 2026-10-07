@extends('layouts.admin')
@section('title', 'Advertising')
@section('content')
    @include('admin.advertising._nav')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Advertising</h1>
        <a href="{{ route('admin.ads.index') }}" class="text-sm text-zinc-600 underline">Image and HTML ads</a>
    </div>
    <p class="mb-4 max-w-2xl text-sm text-zinc-600">Direct campaign counts below are recorded by this site. Google AdSense revenue is not shown here, and AdSense is not treated as connected until a real publisher ID is saved and confirmed in your AdSense account.</p>
    <dl class="mb-6 grid gap-3 sm:grid-cols-3">
        @foreach ([
            'Active campaigns' => $campaigns,
            'Active networks' => $networks,
            'Placements' => $placements,
            'Impressions today' => $impressions,
            'Clicks today' => $clicks,
            'AdSense publisher ID' => $publisherSaved ? 'Saved' : 'Not set',
        ] as $label => $value)
            <div class="rounded bg-white px-4 py-3 shadow-sm">
                <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ $label }}</dt>
                <dd class="mt-1 text-2xl font-semibold">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
    <p class="mb-2 text-sm text-zinc-500">AdSense switch: {{ $adsenseOn ? 'On' : 'Off' }}. {{ $publisherSaved ? 'A publisher ID is saved.' : 'No publisher ID is saved.' }}</p>
    <h2 class="mb-2 font-[Rubik,sans-serif] text-lg">Campaigns ending within 7 days</h2>
    @if ($expiring->isEmpty())
        <p class="text-sm text-zinc-500">None.</p>
    @else
        <ul class="rounded bg-white shadow-sm">
            @foreach ($expiring as $campaign)
                <li class="border-b border-zinc-100 px-4 py-3 text-sm">{{ $campaign->name }} · {{ $campaign->end_at?->timezone('Africa/Lagos')->format('j M Y, g:i A') }}</li>
            @endforeach
        </ul>
    @endif
@endsection
