@extends('layouts.admin')
@section('title', 'Breaking news')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Breaking news</h1>
        <a href="{{ route('admin.breaking.create') }}" class="rounded bg-eribs px-3 py-2 text-sm font-semibold text-white">New item</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @forelse ($items as $item)
            <li class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.breaking.edit', $item) }}">{{ $item->title }}</a>
                <span>{{ $item->is_active ? 'Active' : 'Off' }}</span>
            </li>
        @empty
            <li class="px-4 py-6 text-sm text-zinc-500">No breaking news yet.</li>
        @endforelse
    </ul>
@endsection
