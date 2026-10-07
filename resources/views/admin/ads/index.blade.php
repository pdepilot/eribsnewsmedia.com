@extends('layouts.admin')
@section('title', 'Advertisements')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Advertisements</h1>
        <a href="{{ route('admin.ads.create') }}" class="rounded bg-eribs px-3 py-2 text-sm font-semibold text-white">New placement</a>
    </div>
    <p class="mb-4 text-sm text-zinc-500">These are direct image and HTML ads. Networks, campaigns, and ads.txt live under Advertising. Snapshot AdSense units stay off until you activate them.</p>
    <ul class="rounded bg-white shadow-sm">
        @foreach ($ads as $ad)
            <li class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.ads.edit', $ad) }}">{{ $ad->name }}</a>
                <span>{{ $ad->placement }} · {{ $ad->is_active ? 'Active' : 'Off' }}</span>
            </li>
        @endforeach
    </ul>
@endsection
