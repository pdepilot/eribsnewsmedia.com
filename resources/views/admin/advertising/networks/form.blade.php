@extends('layouts.admin')
@section('title', 'Ad network')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $network->exists ? 'Edit network' : 'New network' }}</h1>
    @if ($network->type === 'ad_manager')
        <p class="mb-4 max-w-2xl text-sm text-zinc-600">This source is reserved for a later Google Ad Manager integration. Public pages do not render it.</p>
    @endif
    <form method="post" action="{{ $network->exists ? route('admin.advertising.networks.update', $network) : route('admin.advertising.networks.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($network->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $network->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Slug <input name="slug" value="{{ old('slug', $network->slug) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Type
            <select name="type" class="rounded border border-zinc-300 px-3 py-2">
                @foreach (\App\Models\AdNetwork::TYPES as $type)
                    <option value="{{ $type }}" @selected(old('type', $network->type) === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Public publisher ID <input name="publisher_id" value="{{ old('publisher_id', $network->publisher_id) }}" placeholder="ca-pub-" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Priority <input type="number" name="priority" value="{{ old('priority', $network->priority) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Configuration JSON <textarea name="configuration" rows="5" class="rounded border border-zinc-300 px-3 py-2 font-mono text-xs">{{ old('configuration', $network->configuration ? json_encode($network->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea></label>
        @if ($network->hasCredentials())
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="clear_credentials" value="1"> Remove the saved credential. New API keys are not stored.</label>
        @endif
        <input type="hidden" name="enabled" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $network->enabled))> Enabled</label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($network->exists && ! in_array($network->slug, ['google-adsense', 'google-ad-manager'], true))
        <form method="post" action="{{ route('admin.advertising.networks.destroy', $network) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
