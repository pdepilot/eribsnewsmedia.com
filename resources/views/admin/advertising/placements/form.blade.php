@extends('layouts.admin')
@section('title', 'Ad placement')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $placement->exists ? 'Edit placement' : 'New placement' }}</h1>
    <form method="post" action="{{ $placement->exists ? route('admin.advertising.placements.update', $placement) : route('admin.advertising.placements.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($placement->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $placement->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Slug <input name="slug" value="{{ old('slug', $placement->slug) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Description <input name="description" value="{{ old('description', $placement->description) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Device
            <select name="device" class="rounded border border-zinc-300 px-3 py-2">
                @foreach (\App\Models\AdPlacement::DEVICES as $device)
                    <option value="{{ $device }}" @selected(old('device', $placement->device) === $device)>{{ $device }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Sort <input type="number" name="sort_order" value="{{ old('sort_order', $placement->sort_order) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <input type="hidden" name="enabled" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $placement->enabled))> Enabled</label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($placement->exists)
        <form method="post" action="{{ route('admin.advertising.placements.destroy', $placement) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
