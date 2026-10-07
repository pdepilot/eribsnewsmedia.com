<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $lead = Article::query()
            ->published()
            ->with(['author', 'category'])
            ->withCount('comments')
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->limit(12)
            ->get();

        $popular = Article::query()
            ->published()
            ->with(['category'])
            ->withCount('comments')
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->limit(4)
            ->get();

        $latest = Article::query()
            ->published()
            ->with(['author', 'category'])
            ->withCount('comments')
            ->latest('published_at')
            ->limit(6)
            ->get();

        $sections = Category::query()
            ->where('show_on_home', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                return [
                    'category' => $category,
                    'articles' => Article::query()
                        ->published()
                        ->with(['author'])
                        ->withCount('comments')
                        ->where('category_id', $category->id)
                        ->latest('published_at')
                        ->limit(5)
                        ->get(),
                ];
            });

        $slides = $lead->chunk(6)->values();

        return view('public.home', compact('slides', 'popular', 'latest', 'sections'));
    }
}
