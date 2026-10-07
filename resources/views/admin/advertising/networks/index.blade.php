@extends('layouts.admin')
@section('title', 'Ad networks')
@section('content')
    @include('admin.advertising._nav')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Networks</h1>
        <a href="{{ route('admin.advertising.networks.create') }}" class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">New network</a>
    </div>
    <p class="mb-4 text-sm text-zinc-500">Google Ad Manager is reserved. This desk does not serve Ad Manager tags.</p>
    <ul class="rounded bg-white shadow-sm">
        @forelse ($networks as $network)
            <li class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.advertising.networks.edit', $network) }}">{{ $network->name }}</a>
                <span>{{ $network->type }} · priority {{ $network->priority }} · {{ $network->enabled ? 'On' : 'Off' }}{{ $network->type === 'ad_manager' ? ' · reserved' : '' }}</span>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-zinc-500">No networks yet.</li>
        @endforelse
    </ul>
@endsection
