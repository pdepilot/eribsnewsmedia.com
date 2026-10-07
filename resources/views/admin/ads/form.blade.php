@extends('layouts.admin')
@section('title', 'Advertisement')
@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $ad->exists ? 'Edit advertisement' : 'New advertisement' }}</h1>
    <form method="post" action="{{ $ad->exists ? route('admin.ads.update', $ad) : route('admin.ads.store') }}" enctype="multipart/form-data" class="grid max-w-2xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($ad->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $ad->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Placement
            <select name="placement" class="rounded border border-zinc-300 px-3 py-2">
                @foreach (\App\Models\AdPlacement::query()->orderBy('sort_order')->get() as $placement)
                    <option value="{{ $placement->slug }}" @selected(old('placement', $ad->placement) === $placement->slug)>{{ $placement->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Type
            <select name="type" class="rounded border border-zinc-300 px-3 py-2">
                <option value="html" @selected(old('type', $ad->type) === 'html')>HTML / AdSense code</option>
                <option value="image" @selected(old('type', $ad->type) === 'image')>Image</option>
            </select>
        </label>
        <label class="grid gap-1 text-sm">Code <textarea name="code" rows="6" class="rounded border border-zinc-300 px-3 py-2 font-mono text-xs">{{ old('code', $ad->code) }}</textarea></label>
        <label class="grid gap-1 text-sm">Image <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></label>
        <label class="grid gap-1 text-sm">Target URL <input name="target_url" value="{{ old('target_url', $ad->target_url) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Alt text <input name="alt_text" value="{{ old('alt_text', $ad->alt_text) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Starts <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $ad->starts_at?->format('Y-m-d\TH:i')) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Ends <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $ad->ends_at?->format('Y-m-d\TH:i')) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $ad->is_active))> Active</label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($ad->exists)
        <form method="post" action="{{ route('admin.ads.destroy', $ad) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
