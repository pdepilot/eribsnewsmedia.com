<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Services\ArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request, ?string $status = null): View
    {
        $this->authorize('viewAny', Article::class);

        $articles = Article::query()
            ->with(['author', 'category'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when(
                ! $request->user()->hasPermission('articles.review'),
                fn ($query) => $query->where('author_id', $request->user()->authorProfile?->id ?? 0),
            )
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.articles.index', compact('articles', 'status'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Article::class);

        return view('admin.articles.form', [
            'article' => new Article(['status' => ArticleStatus::Draft]),
            'categories' => Category::query()->orderBy('name')->get(),
            'authors' => Author::query()->orderBy('name')->get(),
            'canReview' => $request->user()->hasPermission('articles.review'),
        ]);
    }

    public function store(ArticleRequest $request, ArticleService $articles): RedirectResponse
    {
        $article = $articles->create(
            $request->user(),
            $request->validated(),
            $request->file('featured_image'),
        );

        $this->runAction($request, $articles, $article);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Article saved.');
    }

    public function edit(Request $request, Article $article): View
    {
        $this->authorize('view', $article);
        $article->load('tags');

        return view('admin.articles.form', [
            'article' => $article,
            'categories' => Category::query()->orderBy('name')->get(),
            'authors' => Author::query()->orderBy('name')->get(),
            'canReview' => $request->user()->hasPermission('articles.review'),
        ]);
    }

    public function update(ArticleRequest $request, Article $article, ArticleService $articles): RedirectResponse
    {
        $article = $articles->update(
            $article,
            $request->user(),
            $request->validated(),
            $request->file('featured_image'),
        );

        $this->runAction($request, $articles, $article);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Article updated.');
    }

    public function destroy(Article $article, ArticleService $articles): RedirectResponse
    {
        $this->authorize('delete', $article);
        $status = $article->status->value;
        $articles->delete($article);

        return redirect()->route('admin.articles.index', $status)->with('status', 'Article deleted.');
    }

    private function runAction(ArticleRequest $request, ArticleService $articles, Article $article): void
    {
        $action = $request->input('action', 'save');
        $article = $article->refresh();

        if ($action === 'submit') {
            $this->authorize('submit', $article);
            $articles->submit($article);
        } elseif ($action === 'approve') {
            $this->authorize('review', $article);
            $articles->approve($article, $request->user());
        } elseif ($action === 'reject') {
            $this->authorize('review', $article);
            $articles->reject($article, $request->user(), (string) $request->input('rejection_reason'));
        } elseif ($action === 'publish') {
            $this->authorize('publish', $article);
            $articles->publish($article, $request->user());
        } elseif ($action === 'schedule') {
            $this->authorize('publish', $article);
            $articles->schedule($article, $request->user(), new \DateTimeImmutable((string) $request->input('scheduled_at')));
        } elseif ($action === 'archive') {
            $this->authorize('publish', $article);
            $articles->archive($article);
        }
    }
}
