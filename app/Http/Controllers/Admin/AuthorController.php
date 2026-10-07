<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthorRequest;
use App\Models\Author;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('authors.manage'), 403);

        return view('admin.authors.index', [
            'authors' => Author::query()->with('user')->withCount('articles')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasPermission('authors.manage'), 403);

        return view('admin.authors.form', [
            'author' => new Author,
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(AuthorRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Author::query()->create([
            'name' => $data['name'],
            'slug' => Author::uniqueSlug($data['slug'] ?: $data['name']),
            'bio' => $data['bio'] ?? null,
            'email' => $data['email'] ?? null,
            'twitter' => $data['twitter'] ?? null,
            'user_id' => $data['user_id'] ?? null,
        ]);

        return redirect()->route('admin.authors.index')->with('status', 'Author created.');
    }

    public function edit(Author $author): View
    {
        abort_unless(auth()->user()->hasPermission('authors.manage'), 403);

        return view('admin.authors.form', [
            'author' => $author,
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function update(AuthorRequest $request, Author $author): RedirectResponse
    {
        $data = $request->validated();
        $author->update([
            'name' => $data['name'],
            'slug' => Author::uniqueSlug($data['slug'] ?: $data['name'], $author->id),
            'bio' => $data['bio'] ?? null,
            'email' => $data['email'] ?? null,
            'twitter' => $data['twitter'] ?? null,
            'user_id' => $data['user_id'] ?? null,
        ]);

        return redirect()->route('admin.authors.index')->with('status', 'Author updated.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('authors.manage'), 403);

        if ($author->articles()->exists()) {
            return back()->withErrors(['author' => 'Reassign this author’s articles before deleting the profile.']);
        }

        $author->delete();

        return redirect()->route('admin.authors.index')->with('status', 'Author deleted.');
    }
}
