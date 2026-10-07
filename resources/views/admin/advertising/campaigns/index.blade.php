@extends('layouts.admin')
@section('title', 'Campaigns')
@section('content')
    @include('admin.advertising._nav')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Campaigns</h1>
        <a href="{{ route('admin.advertising.campaigns.create') }}" class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">New campaign</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @forelse ($campaigns as $campaign)
            <li class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.advertising.campaigns.edit', $campaign) }}">{{ $campaign->name }}</a>
                <span>{{ $campaign->placement?->slug }} · {{ $campaign->status }} · {{ $campaign->impressions }} views · {{ $campaign->clicks }} clicks</span>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-zinc-500">No campaigns yet.</li>
        @endforelse
    </ul>
@endsection
