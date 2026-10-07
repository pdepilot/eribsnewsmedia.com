@extends('layouts.admin')
@section('title', 'Advertisers')
@section('content')
    @include('admin.advertising._nav')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Advertisers</h1>
        <a href="{{ route('admin.advertising.advertisers.create') }}" class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">New advertiser</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @forelse ($advertisers as $advertiser)
            <li class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.advertising.advertisers.edit', $advertiser) }}">{{ $advertiser->name }}</a>
                <span>{{ $advertiser->company ?: 'No company' }} · {{ $advertiser->status }}</span>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-zinc-500">No advertisers yet.</li>
        @endforelse
    </ul>
@endsection
