@extends('layouts.admin')
@section('title', 'ads.txt')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-2 font-[Rubik,sans-serif] text-2xl">ads.txt</h1>
    <p class="mb-4 max-w-2xl text-sm text-zinc-600">One record per line: domain, publisher id, DIRECT or RESELLER, and an optional certification id. Nothing is added for you. The public file is /ads.txt.</p>
    <form method="post" action="{{ route('admin.advertising.ads-txt.update') }}" class="grid max-w-2xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @method('PUT')
        <textarea name="lines" rows="12" class="rounded border border-zinc-300 px-3 py-2 font-mono text-xs">{{ old('lines', $lines) }}</textarea>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save ads.txt</button>
    </form>
@endsection
