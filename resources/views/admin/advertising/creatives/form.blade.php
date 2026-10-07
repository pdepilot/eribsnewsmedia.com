@extends('layouts.admin')
@section('title', 'Creative')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $creative->exists ? 'Edit creative' : 'New creative' }}</h1>
    <form method="post" action="{{ $creative->exists ? route('admin.advertising.creatives.update', $creative) : route('admin.advertising.creatives.store') }}" enctype="multipart/form-data" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($creative->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $creative->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Advertiser
            <select name="advertiser_id" class="rounded border border-zinc-300 px-3 py-2">
                <option value="">None</option>
                @foreach ($advertisers as $advertiser)
                    <option value="{{ $advertiser->id }}" @selected((string) old('advertiser_id', $creative->advertiser_id) === (string) $advertiser->id)>{{ $advertiser->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Type
            <select name="type" class="rounded border border-zinc-300 px-3 py-2">
                <option value="image" @selected(old('type', $creative->type) === 'image')>Image</option>
                <option value="html" @selected(old('type', $creative->type) === 'html')>HTML</option>
            </select>
        </label>
        <label class="grid gap-1 text-sm">Image <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></label>
        @if ($creative->imageUrl())
            <img src="{{ $creative->imageUrl() }}" alt="" class="max-h-24">
        @endif
        <label class="grid gap-1 text-sm">HTML <textarea name="html" rows="6" class="rounded border border-zinc-300 px-3 py-2 font-mono text-xs">{{ old('html', $creative->html) }}</textarea></label>
        <label class="grid gap-1 text-sm">Alt text <input name="alt_text" value="{{ old('alt_text', $creative->alt_text) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($creative->exists)
        <form method="post" action="{{ route('admin.advertising.creatives.destroy', $creative) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
