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
    <h2 class="mb-2 mt-6 font-[Rubik,sans-serif] text-lg">Structured entries</h2>
    <form method="post" action="{{ route('admin.advertising.ads-txt.entries.store') }}" class="mb-4 grid max-w-2xl gap-3 rounded bg-white p-4 shadow-sm sm:grid-cols-2">
        @csrf
        <label class="grid gap-1 text-sm">System <input name="advertising_system" value="{{ old('advertising_system') }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Publisher account <input name="publisher_account_id" value="{{ old('publisher_account_id') }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Relationship
            <select name="relationship" class="rounded border border-zinc-300 px-3 py-2">
                <option value="DIRECT">DIRECT</option>
                <option value="RESELLER">RESELLER</option>
            </select>
        </label>
        <label class="grid gap-1 text-sm">Certification id <input name="certification_authority_id" value="{{ old('certification_authority_id') }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white sm:col-span-2">Add entry</button>
    </form>
    <ul class="mb-6 max-w-2xl rounded bg-white shadow-sm">
        @forelse ($entries as $entry)
            <li class="flex items-center justify-between gap-3 border-b border-zinc-100 px-4 py-3 text-sm">
                <span class="font-mono text-xs">{{ $entry->line() }}</span>
                <form method="post" action="{{ route('admin.advertising.ads-txt.entries.destroy', $entry) }}">@csrf @method('DELETE')<button class="text-red-700">Remove</button></form>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-zinc-500">No structured entries yet.</li>
        @endforelse
    </ul>
    <h2 class="mb-2 font-[Rubik,sans-serif] text-lg">Preview of /ads.txt</h2>
    <pre class="max-w-2xl overflow-x-auto rounded bg-white p-4 font-mono text-xs">{{ $preview }}</pre>
@endsection
