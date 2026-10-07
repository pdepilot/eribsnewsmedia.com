@extends('layouts.admin')
@section('title', 'Category')
@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $category->exists ? 'Edit category' : 'New category' }}</h1>
    <form method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $category->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Slug <input name="slug" value="{{ old('slug', $category->slug) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Description <textarea name="description" rows="4" class="rounded border border-zinc-300 px-3 py-2">{{ old('description', $category->description) }}</textarea></label>
        <label class="grid gap-1 text-sm">Sort order <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <input type="hidden" name="show_on_home" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $category->show_on_home))> Show on homepage</label>
        <input type="hidden" name="show_in_nav" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_in_nav" value="1" @checked(old('show_in_nav', $category->show_in_nav))> Show in the top menu</label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($category->exists)
        <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="mt-4">
            @csrf @method('DELETE')
            <button class="text-sm text-red-700">Delete</button>
        </form>
    @endif
@endsection
