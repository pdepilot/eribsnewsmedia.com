@extends('layouts.admin')
@section('title', 'Ad units')
@section('content')
    @include('admin.advertising._nav')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Ad units</h1>
        <a href="{{ route('admin.advertising.units.create') }}" class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">New unit</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @forelse ($units as $unit)
            <li class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.advertising.units.edit', $unit) }}">{{ $unit->name }}</a>
                <span>{{ $unit->network?->name }} · {{ $unit->placement?->slug ?: 'No placement' }} · {{ $unit->enabled ? 'On' : 'Off' }}</span>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-zinc-500">No ad units yet.</li>
        @endforelse
    </ul>
@endsection
