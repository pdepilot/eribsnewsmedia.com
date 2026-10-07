@extends('layouts.admin')
@section('title', 'Advertiser')
@section('content')
    @include('admin.advertising._nav')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $advertiser->exists ? 'Edit advertiser' : 'New advertiser' }}</h1>
    <form method="post" action="{{ $advertiser->exists ? route('admin.advertising.advertisers.update', $advertiser) : route('admin.advertising.advertisers.store') }}" class="grid max-w-xl gap-3 rounded bg-white p-4 shadow-sm">
        @csrf
        @if ($advertiser->exists) @method('PUT') @endif
        <label class="grid gap-1 text-sm">Name <input name="name" value="{{ old('name', $advertiser->name) }}" class="rounded border border-zinc-300 px-3 py-2" required></label>
        <label class="grid gap-1 text-sm">Company <input name="company" value="{{ old('company', $advertiser->company) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Email <input type="email" name="email" value="{{ old('email', $advertiser->email) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Phone <input name="phone" value="{{ old('phone', $advertiser->phone) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Website <input name="website" value="{{ old('website', $advertiser->website) }}" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Status
            <select name="status" class="rounded border border-zinc-300 px-3 py-2">
                @foreach (\App\Models\Advertiser::STATUSES as $status)
                    <option value="{{ $status }}" @selected(old('status', $advertiser->status) === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Save</button>
    </form>
    @if ($advertiser->exists)
        <form method="post" action="{{ route('admin.advertising.advertisers.destroy', $advertiser) }}" class="mt-4">@csrf @method('DELETE')<button class="text-sm text-red-700">Delete</button></form>
    @endif
@endsection
