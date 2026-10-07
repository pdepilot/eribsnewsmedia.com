@extends('layouts.admin')
@section('title', 'Ad placements')
@section('content')
    @include('admin.advertising._nav')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Placements</h1>
        <a href="{{ route('admin.advertising.placements.create') }}" class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">New placement</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @forelse ($placements as $placement)
            <li class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.advertising.placements.edit', $placement) }}">{{ $placement->name }}</a>
                <span>{{ $placement->slug }} · {{ $placement->device }} · {{ $placement->enabled ? 'On' : 'Off' }}</span>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-zinc-500">No placements yet.</li>
        @endforelse
    </ul>
@endsection
