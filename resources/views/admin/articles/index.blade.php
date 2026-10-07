@extends('layouts.admin')

@section('title', 'Articles')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">{{ $status ? str_replace('_', ' ', $status) : 'Articles' }}</h1>
        <a href="{{ route('admin.articles.create') }}" class="rounded bg-eribs px-3 py-2 text-sm font-semibold text-white">New article</a>
    </div>
    <div class="overflow-x-auto rounded bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-zinc-200 text-zinc-500">
                <tr>
                    <th class="px-4 py-3">Image</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Author</th>
                    <th class="px-4 py-3">Posted</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articles as $article)
                    <tr class="border-b border-zinc-100">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="block h-14 w-20 overflow-hidden bg-zinc-100">
                                @if ($article->featuredImageUrl())
                                    <img src="{{ $article->featuredImageUrl() }}" alt="{{ $article->featured_image_alt ?: $article->title }}" class="h-full w-full object-cover">
                                @endif
                            </a>
                        </td>
                        <td class="px-4 py-3"><a class="font-semibold" href="{{ route('admin.articles.edit', $article) }}">{{ $article->title }}</a></td>
                        <td class="px-4 py-3">{{ $article->status->label() }}</td>
                        <td class="px-4 py-3">{{ $article->category?->name }}</td>
                        <td class="px-4 py-3">{{ $article->author?->name }}</td>
                        <td class="px-4 py-3">
                            @if ($article->postedLabel())
                                {{ $article->postedLabel() }}
                            @elseif ($article->scheduled_at)
                                Scheduled {{ $article->scheduled_at->timezone('Africa/Lagos')->format('j M Y, g:i A') }}
                            @else
                                Not posted
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="row-actions">
                                <a class="row-edit" href="{{ route('admin.articles.edit', $article) }}">Edit</a>
                                @can('delete', $article)
                                    <form method="post" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Delete this article?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="row-delete">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-zinc-500" colspan="7">No articles in this list.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $articles->links() }}</div>
@endsection
