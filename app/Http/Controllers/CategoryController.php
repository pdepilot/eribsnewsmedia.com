<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $articles = $category->articles()
            ->published()
            ->with(['author', 'category'])
            ->withCount('comments')
            ->latest('published_at')
            ->paginate(12);

        return view('public.listing', [
            'heading' => $category->name,
            'intro' => $category->description,
            'articles' => $articles,
        ]);
    }
}
