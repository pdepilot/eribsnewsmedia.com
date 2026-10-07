@extends('layouts.admin')
@section('title', 'Creatives')
@section('content')
    @include('admin.advertising._nav')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Creatives</h1>
        <a href="{{ route('admin.advertising.creatives.create') }}" class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">New creative</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @forelse ($creatives as $creative)
            <li class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.advertising.creatives.edit', $creative) }}">{{ $creative->name }}</a>
                <span>{{ $creative->type }} · {{ $creative->advertiser?->name ?: 'No advertiser' }}</span>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-zinc-500">No creatives yet.</li>
        @endforelse
    </ul>
@endsection
