@extends('layouts.admin')

@section('title', 'Comments')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="font-[Rubik,sans-serif] text-2xl">Comments</h1>
    </div>
    <div class="grid gap-3">
        @forelse ($comments as $comment)
            <article class="rounded bg-white p-4 text-sm shadow-sm">
                <p class="font-semibold">{{ $comment->name }} <span class="font-normal text-zinc-500">on {{ $comment->article?->title ?: 'a removed story' }}</span></p>
                <p class="mt-2">{{ $comment->body }}</p>
                <form method="post" action="{{ route('admin.comments.destroy', $comment) }}" class="mt-2" onsubmit="return confirm('Delete this comment?')">
                    @csrf @method('DELETE')
                    <button class="text-red-700">Delete</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-zinc-500">No comments yet.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $comments->links() }}</div>
@endsection
