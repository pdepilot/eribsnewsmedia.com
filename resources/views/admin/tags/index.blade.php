@extends('layouts.admin')
@section('title', 'Tags')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Tags</h1>
        <a href="{{ route('admin.tags.create') }}" class="rounded bg-eribs px-3 py-2 text-sm font-semibold text-white">New tag</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @forelse ($tags as $tag)
            <li class="flex justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.tags.edit', $tag) }}">{{ $tag->name }}</a>
                <span>{{ $tag->articles_count }}</span>
            </li>
        @empty
            <li class="px-4 py-6 text-sm text-zinc-500">No tags yet.</li>
        @endforelse
    </ul>
@endsection
