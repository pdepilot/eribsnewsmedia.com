<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function show(Author $author): View
    {
        $articles = $author->articles()
            ->published()
            ->with(['author', 'category'])
            ->withCount('comments')
            ->latest('published_at')
            ->paginate(12);

        return view('public.listing', [
            'heading' => $author->name,
            'intro' => $author->bio,
            'articles' => $articles,
        ]);
    }
}
