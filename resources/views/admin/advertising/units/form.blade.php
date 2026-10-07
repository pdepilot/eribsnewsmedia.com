@extends('layouts.admin')
@section('title', 'Ad unit')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-2 font-[Rubik,sans-serif] text-2xl">{{ $unit->exists ? 'Edit ad unit' : 'New ad unit' }}</h1>
    <p class="mb-4 max-w-2xl text-sm text-zinc-600">For AdSense, enter the client and slot from the unit Google gives you. For another network, paste that network’s tag. This form does not invent either one.</p>
    <form method="post" action="{{ $unit->exists ? route('admin.advertising.units.update', $unit) : route('admin.advertising.units.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($unit->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $unit->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Network
            <select name="ad_network_id" class="rounded border border-zinc-300 px-3 py-2">
                @foreach ($networks as $network)
                    <option value="{{ $network->id }}" @selected((string) old('ad_network_id', $unit->ad_network_id) === (string) $network->id)>{{ $network->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Placement
            <select name="ad_placement_id" class="rounded border border-zinc-300 px-3 py-2">
                <option value="">None</option>
                @foreach ($placements as $placement)
                    <option value="{{ $placement->id }}" @selected((string) old('ad_placement_id', $unit->ad_placement_id) === (string) $placement->id)>{{ $placement->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Ad client <input name="ad_client" value="{{ old('ad_client', $unit->ad_client) }}" placeholder="ca-pub-" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Ad slot <input name="ad_slot" value="{{ old('ad_slot', $unit->ad_slot) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Format
            <select name="format" class="rounded border border-zinc-300 px-3 py-2">
                <option value="">Not set</option>
                @foreach (\App\Models\AdUnit::FORMATS as $format)
                    <option value="{{ $format }}" @selected(old('format', $unit->format) === $format)>{{ $format }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Device
            <select name="device" class="rounded border border-zinc-300 px-3 py-2">
                @foreach (\App\Models\AdPlacement::DEVICES as $device)
                    <option value="{{ $device }}" @selected(old('device', $unit->device) === $device)>{{ $device }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Page
            <select name="page_target" class="rounded border border-zinc-300 px-3 py-2">
                @foreach (\App\Services\Advertising\AdManagerService::PAGES as $page)
                    <option value="{{ $page }}" @selected(old('page_target', $unit->page_target ?: 'all') === $page)>{{ $page }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Priority <input type="number" name="priority" value="{{ old('priority', $unit->priority) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Weight <input type="number" name="weight" min="1" value="{{ old('weight', $unit->weight ?: 100) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Starts <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $unit->starts_at?->format('Y-m-d\TH:i')) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Ends <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $unit->ends_at?->format('Y-m-d\TH:i')) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Network code <textarea name="markup" rows="6" class="rounded border border-zinc-300 px-3 py-2 font-mono text-xs">{{ old('markup', $unit->markup) }}</textarea></label>
        <label class="grid gap-1 text-sm">Fallback code <textarea name="fallback_code" rows="4" class="rounded border border-zinc-300 px-3 py-2 font-mono text-xs">{{ old('fallback_code', $unit->fallback_code) }}</textarea></label>
        <input type="hidden" name="responsive" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="responsive" value="1" @checked(old('responsive', $unit->responsive))> Responsive</label>
        <input type="hidden" name="enabled" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $unit->enabled))> Enabled</label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($unit->exists)
        <form method="post" action="{{ route('admin.advertising.units.destroy', $unit) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
