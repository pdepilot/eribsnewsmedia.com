@extends('layouts.admin')

@section('title', $article->exists ? 'Edit article' : 'New article')

@section('content')
    <h1 class="mb-4 font-[Rubik,sans-serif] text-2xl">{{ $article->exists ? 'Edit article' : 'New article' }}</h1>
    @if ($article->exists)
        <p class="mb-4 text-sm text-zinc-500">Status: {{ $article->status->label() }} @if($article->slug) · /article/{{ $article->slug }} @endif</p>
        @if ($article->rejection_reason)
            <p class="mb-4 rounded bg-amber-50 px-4 py-3 text-sm text-amber-900">Returned: {{ $article->rejection_reason }}</p>
        @endif
    @endif
    <form method="post" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}" enctype="multipart/form-data" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
        @csrf
        @if ($article->exists)
            @method('PUT')
        @endif
        <div class="grid gap-4 rounded bg-white p-4 shadow-sm">
            <label class="grid gap-1 text-sm">Title
                <input name="title" value="{{ old('title', $article->title) }}" class="rounded border border-zinc-300 px-3 py-2" required>
            </label>
            <div class="article-upload">
                <div>
                    <p class="article-upload-label">Article image</p>
                    <p class="article-upload-hint">JPEG, PNG, GIF, or WebP. This is the picture on the story and in the published list.</p>
                    <label class="article-upload-btn">
                        Choose image
                        <input id="article-image" type="file" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp">
                    </label>
                </div>
                <div class="article-upload-preview">
                    <img id="article-image-preview" src="{{ $article->featuredImageUrl() }}" alt="{{ $article->featured_image_alt ?: $article->title }}" @unless($article->featuredImageUrl()) hidden @endunless>
                    @unless ($article->featuredImageUrl())
                        <span id="article-image-empty">No image yet</span>
                    @endunless
                </div>
                <label class="grid gap-1 text-sm sm:col-span-2">Image description
                    <input name="featured_image_alt" value="{{ old('featured_image_alt', $article->featured_image_alt) }}" class="rounded border border-zinc-300 px-3 py-2" placeholder="What the picture shows">
                </label>
                @if ($article->featuredImageUrl())
                    <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="remove_featured_image" value="1"> Remove the current image</label>
                @endif
            </div>
            <label class="grid gap-1 text-sm">Slug
                <input name="slug" value="{{ old('slug', $article->slug) }}" class="rounded border border-zinc-300 px-3 py-2" placeholder="Leave blank to keep or generate">
            </label>
            <label class="grid gap-1 text-sm">Excerpt
                <textarea name="excerpt" rows="3" class="rounded border border-zinc-300 px-3 py-2">{{ old('excerpt', $article->excerpt) }}</textarea>
            </label>
            <label class="grid gap-1 text-sm">Body
                <textarea name="body" rows="14" class="rounded border border-zinc-300 px-3 py-2">{{ old('body', $article->body) }}</textarea>
            </label>
            <label class="grid gap-1 text-sm">Tags
                <input name="tags" value="{{ old('tags', $article->exists ? $article->tags->pluck('name')->join(', ') : '') }}" class="rounded border border-zinc-300 px-3 py-2" placeholder="Comma separated">
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="grid gap-1 text-sm">SEO title
                    <input name="seo_title" value="{{ old('seo_title', $article->seo_title) }}" class="rounded border border-zinc-300 px-3 py-2">
                </label>
                <label class="grid gap-1 text-sm">Canonical URL
                    <input name="canonical_url" value="{{ old('canonical_url', $article->canonical_url) }}" class="rounded border border-zinc-300 px-3 py-2">
                </label>
                <label class="grid gap-1 text-sm sm:col-span-2">Meta description
                    <textarea name="seo_description" rows="2" class="rounded border border-zinc-300 px-3 py-2">{{ old('seo_description', $article->seo_description) }}</textarea>
                </label>
                <label class="grid gap-1 text-sm">Open Graph title
                    <input name="og_title" value="{{ old('og_title', $article->og_title) }}" class="rounded border border-zinc-300 px-3 py-2">
                </label>
                <label class="grid gap-1 text-sm">Open Graph image URL
                    <input name="og_image" value="{{ old('og_image', $article->og_image) }}" class="rounded border border-zinc-300 px-3 py-2">
                </label>
                <label class="grid gap-1 text-sm sm:col-span-2">Open Graph description
                    <textarea name="og_description" rows="2" class="rounded border border-zinc-300 px-3 py-2">{{ old('og_description', $article->og_description) }}</textarea>
                </label>
            </div>
        </div>
        <div class="grid content-start gap-4">
            <div class="grid gap-3 rounded bg-white p-4 shadow-sm text-sm">
                <label class="grid gap-1">Category
                    <select name="category_id" class="rounded border border-zinc-300 px-3 py-2">
                        <option value="">None</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $article->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                @if ($canReview)
                    <label class="grid gap-1">Author
                        <select name="author_id" class="rounded border border-zinc-300 px-3 py-2">
                            <option value="">My profile</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}" @selected(old('author_id', $article->author_id) == $author->id)>{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                @foreach (['is_breaking' => 'Breaking', 'is_featured' => 'Featured', 'is_trending' => 'Trending', 'is_opinion' => 'Opinion'] as $field => $label)
                    <input type="hidden" name="{{ $field }}" value="0">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $article->{$field}))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <div class="grid gap-2 rounded bg-white p-4 shadow-sm text-sm">
                <button class="rounded bg-zinc-900 px-3 py-2 font-semibold text-white" name="action" value="save">Save</button>
                @if (! $article->exists || in_array($article->status->value, ['draft', 'rejected'], true))
                    <button class="rounded border border-zinc-300 px-3 py-2" name="action" value="submit">Submit for review</button>
                @endif
                @if ($article->exists && auth()->user()->can('review', $article) && $article->status->value === 'pending_review')
                    <button class="rounded bg-emerald-700 px-3 py-2 font-semibold text-white" name="action" value="approve">Approve</button>
                @endif
                @if ($article->exists && auth()->user()->can('review', $article) && in_array($article->status->value, ['pending_review', 'approved'], true))
                    <label class="grid gap-1">Rejection note
                        <textarea name="rejection_reason" rows="3" class="rounded border border-zinc-300 px-3 py-2">{{ old('rejection_reason') }}</textarea>
                    </label>
                    <button class="rounded border border-red-300 px-3 py-2 text-red-700" name="action" value="reject">Reject</button>
                @endif
                @if ($article->exists && auth()->user()->can('publish', $article) && $article->status->value === 'approved')
                    <button class="rounded bg-eribs px-3 py-2 font-semibold text-white" name="action" value="publish">Publish now</button>
                    <label class="grid gap-1">Schedule
                        <input type="datetime-local" name="scheduled_at" class="rounded border border-zinc-300 px-3 py-2">
                    </label>
                    <button class="rounded border border-zinc-300 px-3 py-2" name="action" value="schedule">Schedule</button>
                @endif
                @if ($article->exists && auth()->user()->can('publish', $article) && $article->status->value === 'published')
                    <button class="rounded border border-zinc-300 px-3 py-2" name="action" value="archive">Archive</button>
                @endif
            </div>
            @if ($article->exists && auth()->user()->can('delete', $article))
                <button form="delete-article" type="submit" class="row-delete row-delete-block">Delete article</button>
            @endif
        </div>
    </form>
    @if ($article->exists)
        <form id="delete-article" method="post" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Delete this article?')">
            @csrf
            @method('DELETE')
        </form>
    @endif
    <script>
        document.getElementById('article-image')?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            const preview = document.getElementById('article-image-preview');
            const empty = document.getElementById('article-image-empty');
            if (!file || !preview) {
                return;
            }
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
            if (empty) {
                empty.hidden = true;
            }
        });
    </script>
@endsection
