<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('articles.review'), 403);

        return view('admin.comments.index', [
            'comments' => Comment::query()->with('article')->latest()->paginate(30),
        ]);
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('articles.review'), 403);
        $comment->delete();

        return back()->with('status', 'Comment deleted.');
    }
}
