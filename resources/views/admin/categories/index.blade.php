@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Categories</h1>
        <a href="{{ route('admin.categories.create') }}" class="rounded bg-eribs px-3 py-2 text-sm font-semibold text-white">New category</a>
    </div>
    <div class="rounded bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <tbody>
                @foreach ($categories as $category)
                    <tr class="border-b border-zinc-100">
                        <td class="px-4 py-3"><a href="{{ route('admin.categories.edit', $category) }}">{{ $category->name }}</a></td>
                        <td class="px-4 py-3">{{ $category->articles_count }} articles</td>
                        <td class="px-4 py-3">{{ $category->show_on_home ? 'On homepage' : '' }}</td>
                        <td class="px-4 py-3">{{ $category->show_in_nav ? 'In menu' : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
