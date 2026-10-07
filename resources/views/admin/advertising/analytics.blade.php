@extends('layouts.admin')
@section('title', 'Advertising analytics')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-2 font-[Rubik,sans-serif] text-2xl">Analytics</h1>
    <p class="mb-4 max-w-2xl text-sm text-zinc-600">These counts are recorded by this site for direct campaigns. Network revenue is not shown.</p>
    <form method="get" class="mb-4 flex flex-wrap items-end gap-2 text-sm">
        <label>Range
            <select name="range" class="rounded border border-zinc-300 px-2 py-1">
                @foreach (['today' => 'Today', 'yesterday' => 'Yesterday', '7' => 'Last 7 days', '30' => 'Last 30 days', 'custom' => 'Custom'] as $value => $label)
                    <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>From <input type="date" name="from" value="{{ $from }}" class="rounded border border-zinc-300 px-2 py-1"></label>
        <label>To <input type="date" name="to" value="{{ $to }}" class="rounded border border-zinc-300 px-2 py-1"></label>
        <button class="rounded bg-zinc-900 px-3 py-1 text-white">Apply</button>
    </form>
    <dl class="mb-6 grid gap-3 sm:grid-cols-3">
        @foreach (['Impressions' => $impressions, 'Clicks' => $clicks, 'CTR' => $ctr.'%'] as $label => $value)
            <div class="rounded bg-white px-4 py-3 shadow-sm">
                <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ $label }}</dt>
                <dd class="mt-1 text-2xl font-semibold">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded bg-white p-4 shadow-sm">
            <h2 class="mb-2 font-semibold">Top placements</h2>
            @forelse ($topPlacements as $row)
                <p class="text-sm">Placement {{ $row->ad_placement_id }} · {{ $row->total }}</p>
            @empty
                <p class="text-sm text-zinc-500">None in this range.</p>
            @endforelse
        </section>
        <section class="rounded bg-white p-4 shadow-sm">
            <h2 class="mb-2 font-semibold">Top ad units</h2>
            @forelse ($topUnits as $row)
                <p class="text-sm">Unit {{ $row->ad_unit_id }} · {{ $row->total }}</p>
            @empty
                <p class="text-sm text-zinc-500">None in this range.</p>
            @endforelse
        </section>
    </div>
@endsection
