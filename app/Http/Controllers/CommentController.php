<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        abort_unless($article->isPublic(), 404);

        if (filled($request->input('website'))) {
            return back();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $article->comments()->create($data);

        return back()->with('status', 'Your comment is posted.');
    }
}
