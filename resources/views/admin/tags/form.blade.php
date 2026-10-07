@extends('layouts.admin')
@section('title', 'Tag')
@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $tag->exists ? 'Edit tag' : 'New tag' }}</h1>
    <form method="post" action="{{ $tag->exists ? route('admin.tags.update', $tag) : route('admin.tags.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($tag->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $tag->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Slug <input name="slug" value="{{ old('slug', $tag->slug) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($tag->exists)
        <form method="post" action="{{ route('admin.tags.destroy', $tag) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
