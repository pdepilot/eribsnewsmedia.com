<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('tags.manage'), 403);

        return view('admin.tags.index', [
            'tags' => Tag::query()->withCount('articles')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasPermission('tags.manage'), 403);

        return view('admin.tags.form', ['tag' => new Tag]);
    }

    public function store(TagRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Tag::query()->create([
            'name' => $data['name'],
            'slug' => Tag::uniqueSlug($data['slug'] ?: $data['name']),
        ]);

        return redirect()->route('admin.tags.index')->with('status', 'Tag created.');
    }

    public function edit(Tag $tag): View
    {
        abort_unless(auth()->user()->hasPermission('tags.manage'), 403);

        return view('admin.tags.form', compact('tag'));
    }

    public function update(TagRequest $request, Tag $tag): RedirectResponse
    {
        $data = $request->validated();
        $tag->update([
            'name' => $data['name'],
            'slug' => Tag::uniqueSlug($data['slug'] ?: $data['name'], $tag->id),
        ]);

        return redirect()->route('admin.tags.index')->with('status', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('tags.manage'), 403);
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('status', 'Tag deleted.');
    }
}
