@extends('layouts.admin')
@section('title', 'Ad networks')
@section('content')
    @include('admin.advertising._nav')
    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Networks</h1>
        <a href="{{ route('admin.advertising.networks.create') }}" class="shrink-0 rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">New network</a>
    </div>
    <ul class="grid gap-3">
        @forelse ($networks as $network)
            <li class="rounded bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('admin.advertising.networks.edit', $network) }}" class="font-medium">{{ $network->name }}</a>
                    <span class="text-sm {{ $network->enabled ? 'text-zinc-900' : 'text-zinc-500' }}">{{ $network->enabled ? 'On' : 'Off' }}</span>
                </div>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-zinc-500">Type</dt>
                        <dd class="mt-1">{{ $network->type }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="text-xs uppercase tracking-wide text-zinc-500">Publisher ID</dt>
                        <dd class="mt-1 break-all">{{ $network->publisher_id ?: 'Not set' }}</dd>
                    </div>
                </dl>
                @if ($network->type === 'ad_manager')
                    <p class="mt-3 text-sm text-zinc-500">Reserved. Public pages do not render this network.</p>
                @elseif (str_contains((string) $network->notes, 'Inactive demo'))
                    <p class="mt-3 text-sm text-zinc-500">Inactive demo. Turn it on only after you paste a real tag into an ad unit.</p>
                @endif
                <a href="{{ route('admin.advertising.networks.edit', $network) }}" class="mt-3 inline-block text-sm underline">Edit</a>
            </li>
        @empty
            <li class="rounded bg-white px-4 py-3 text-sm text-zinc-500 shadow-sm">No networks yet.</li>
        @endforelse
    </ul>
@endsection
