@extends('layouts.admin')
@section('title', 'Advertising settings')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-2 font-[Rubik,sans-serif] text-2xl">Advertising settings</h1>
    <p class="mb-4 max-w-2xl text-sm text-zinc-600">Leave the publisher ID blank until the site owner supplies it. Saving an ID does not mean AdSense is connected. Confirm the ID in your AdSense account. Google Ad Manager is a separate network and is not implemented.</p>
    <form method="post" action="{{ route('admin.advertising.settings.update') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @method('PUT')
        <input type="hidden" name="advertising_enabled" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="advertising_enabled" value="1" @checked((string) old('advertising_enabled', $advertisingOn ? '1' : '0') === '1')> Advertising enabled</label>
        <input type="hidden" name="adsense_enabled" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="adsense_enabled" value="1" @checked((string) old('adsense_enabled', $adsenseOn ? '1' : '0') === '1')> AdSense enabled</label>
        <input type="hidden" name="adsense_auto_ads" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="adsense_auto_ads" value="1" @checked((string) old('adsense_auto_ads', $autoAds ? '1' : '0') === '1')> Auto ads</label>
        <input type="hidden" name="ads_analytics_enabled" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ads_analytics_enabled" value="1" @checked((string) old('ads_analytics_enabled', $analyticsOn ? '1' : '0') === '1')> Record impressions and clicks on this site</label>
        <label class="grid gap-1 text-sm">Maximum ads per page (0 means no limit)
            <input type="number" name="ads_max_per_page" min="0" max="20" value="{{ old('ads_max_per_page', $maxAds) }}" class="rounded border border-zinc-300 px-3 py-2">
        </label>
        <label class="grid gap-1 text-sm">Default network
            <select name="ads_default_network" class="rounded border border-zinc-300 px-3 py-2">
                <option value="">None</option>
                @foreach ($networks as $item)
                    <option value="{{ $item->id }}" @selected((string) old('ads_default_network', $defaultNetwork) === (string) $item->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Publisher ID
            <input name="publisher_id" value="{{ old('publisher_id', $publisherId) }}" placeholder="ca-pub-" class="rounded border border-zinc-300 px-3 py-2">
        </label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save settings</button>
    </form>
@endsection
