@extends('layouts.admin')
@section('title', 'Authors')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Authors</h1>
        <a href="{{ route('admin.authors.create') }}" class="rounded bg-eribs px-3 py-2 text-sm font-semibold text-white">New author</a>
    </div>
    <ul class="rounded bg-white shadow-sm">
        @foreach ($authors as $author)
            <li class="flex justify-between border-b border-zinc-100 px-4 py-3 text-sm">
                <a href="{{ route('admin.authors.edit', $author) }}">{{ $author->name }}</a>
                <span>{{ $author->articles_count }} stories</span>
            </li>
        @endforeach
    </ul>
@endsection
