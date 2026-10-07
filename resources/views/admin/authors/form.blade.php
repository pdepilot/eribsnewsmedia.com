@extends('layouts.admin')
@section('title', 'Author')
@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $author->exists ? 'Edit author' : 'New author' }}</h1>
    <form method="post" action="{{ $author->exists ? route('admin.authors.update', $author) : route('admin.authors.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($author->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $author->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Slug <input name="slug" value="{{ old('slug', $author->slug) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Email <input name="email" value="{{ old('email', $author->email) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">X / Twitter <input name="twitter" value="{{ old('twitter', $author->twitter) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Linked user
            <select name="user_id" class="rounded border border-zinc-300 px-3 py-2">
                <option value="">None</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id', $author->user_id) == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1 text-sm">Bio <textarea name="bio" rows="5" class="rounded border border-zinc-300 px-3 py-2">{{ old('bio', $author->bio) }}</textarea></label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($author->exists)
        <form method="post" action="{{ route('admin.authors.destroy', $author) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
