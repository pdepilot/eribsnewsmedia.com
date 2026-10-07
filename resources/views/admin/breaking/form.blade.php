@extends('layouts.admin')
@section('title', 'Breaking news')
@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $item->exists ? 'Edit breaking news' : 'New breaking news' }}</h1>
    <form method="post" action="{{ $item->exists ? route('admin.breaking.update', $item) : route('admin.breaking.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($item->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Headline <input name="title" value="{{ old('title', $item->title) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Article
            <select name="article_id" class="rounded border border-zinc-300 px-3 py-2">
                <option value="">None</option>
                @foreach ($articles as $article)
                    <option value="{{ $article->id }}" @selected(old('article_id', $item->article_id) == $article->id)>{{ $article->title }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Or URL <input name="url" value="{{ old('url', $item->url) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Starts <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $item->starts_at?->format('Y-m-d\TH:i')) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Ends <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $item->ends_at?->format('Y-m-d\TH:i')) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))> Active</label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($item->exists)
        <form method="post" action="{{ route('admin.breaking.destroy', $item) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
