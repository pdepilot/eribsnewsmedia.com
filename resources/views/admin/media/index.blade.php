@extends('layouts.admin')
@section('title', 'Media')
@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">Media library</h1>
    <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="mb-6 grid gap-3 rounded bg-white p-4 shadow-sm md:grid-cols-2">
        @csrf
        <label class="grid gap-1 text-sm md:col-span-2">Image <input type="file" name="file" accept="image/jpeg,image/png,image/gif,image/webp" required></label>
        <label class="grid gap-1 text-sm">Alt text <input name="alt_text" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm">Credit <input name="credit" class="rounded border border-zinc-300 px-3 py-2"></label>
        <label class="grid gap-1 text-sm md:col-span-2">Caption <input name="caption" class="rounded border border-zinc-300 px-3 py-2"></label>
        <button class="rounded bg-zinc-900 px-3 py-2 text-sm font-semibold text-white">Upload</button>
    </form>
    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @foreach ($media as $item)
            <figure class="rounded bg-white p-3 shadow-sm text-sm">
                <img src="{{ $item->url() }}" alt="{{ $item->alt_text }}" class="mb-2 aspect-video w-full object-cover" loading="lazy">
                <figcaption>{{ $item->filename }}</figcaption>
                <p class="text-zinc-500">{{ $item->width }}×{{ $item->height }} · {{ $item->mime_type }}</p>
                <form method="post" action="{{ route('admin.media.destroy', $item) }}">@csrf @method('DELETE')<button class="text-red-700">Delete</button></form>
            </figure>
        @endforeach
    </div>
    <div class="mt-4">{{ $media->links() }}</div>
@endsection
