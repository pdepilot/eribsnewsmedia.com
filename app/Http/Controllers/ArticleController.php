<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function latest(): View
    {
        $articles = Article::query()
            ->published()
            ->with(['author', 'category'])
            ->withCount('comments')
            ->latest('published_at')
            ->paginate(12);

        return view('public.listing', [
            'heading' => 'Latest news',
            'intro' => null,
            'articles' => $articles,
        ]);
    }

    public function show(Article $article, ArticleService $articles): View
    {
        abort_unless($article->isPublic(), 404);

        $article->load(['author', 'category', 'tags', 'featuredMedia', 'comments' => fn ($query) => $query->latest()]);
        $article->loadCount('comments');
        $articles->recordView($article);

        $related = Article::query()
            ->published()
            ->with(['category'])
            ->withCount('comments')
            ->where('category_id', $article->category_id)
            ->whereKeyNot($article->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('public.articles.show', compact('article', 'related'));
    }
}
