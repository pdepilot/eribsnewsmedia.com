@extends('layouts.admin')
@section('title', 'Campaign')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $campaign->exists ? 'Edit campaign' : 'New campaign' }}</h1>
    <form method="post" action="{{ $campaign->exists ? route('admin.advertising.campaigns.update', $campaign) : route('admin.advertising.campaigns.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($campaign->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $campaign->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Advertiser
            <select name="advertiser_id" class="rounded border border-zinc-300 px-3 py-2">
                @foreach ($advertisers as $advertiser)
                    <option value="{{ $advertiser->id }}" @selected((string) old('advertiser_id', $campaign->advertiser_id) === (string) $advertiser->id)>{{ $advertiser->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Placement
            <select name="ad_placement_id" class="rounded border border-zinc-300 px-3 py-2">
                @foreach ($placements as $placement)
                    <option value="{{ $placement->id }}" @selected((string) old('ad_placement_id', $campaign->ad_placement_id) === (string) $placement->id)>{{ $placement->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Creative
            <select name="creative_id" class="rounded border border-zinc-300 px-3 py-2">
                @foreach ($creatives as $creative)
                    <option value="{{ $creative->id }}" @selected((string) old('creative_id', $campaign->creative_id) === (string) $creative->id)>{{ $creative->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Starts <input type="datetime-local" name="start_at" value="{{ old('start_at', $campaign->start_at?->format('Y-m-d\TH:i')) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Ends <input type="datetime-local" name="end_at" value="{{ old('end_at', $campaign->end_at?->format('Y-m-d\TH:i')) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Budget <input type="number" step="0.01" min="0" name="budget" value="{{ old('budget', $campaign->budget) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Priority <input type="number" name="priority" value="{{ old('priority', $campaign->priority) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Weight <input type="number" min="1" name="weight" value="{{ old('weight', $campaign->weight ?: 100) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Notes <textarea name="notes" rows="3" class="rounded border border-zinc-300 px-3 py-2">{{ old('notes', $campaign->notes) }}</textarea></label>
        <label class="grid gap-1 text-sm">Status
            <select name="status" class="rounded border border-zinc-300 px-3 py-2">
                @foreach (\App\Models\AdCampaign::STATUSES as $status)
                    <option value="{{ $status }}" @selected(old('status', $campaign->status) === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Click URL <input name="click_url" value="{{ old('click_url', $campaign->click_url) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($campaign->exists)
        <form method="post" action="{{ route('admin.advertising.campaigns.destroy', $campaign) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
