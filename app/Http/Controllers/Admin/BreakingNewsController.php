<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BreakingNewsRequest;
use App\Models\Article;
use App\Models\BreakingNews;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BreakingNewsController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('breaking.manage'), 403);

        return view('admin.breaking.index', [
            'items' => BreakingNews::query()->with('article')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasPermission('breaking.manage'), 403);

        return view('admin.breaking.form', [
            'item' => new BreakingNews(['is_active' => true]),
            'articles' => Article::query()->latest('updated_at')->limit(100)->get(),
        ]);
    }

    public function store(BreakingNewsRequest $request): RedirectResponse
    {
        BreakingNews::query()->create($this->payload($request));

        return redirect()->route('admin.breaking.index')->with('status', 'Breaking news saved.');
    }

    public function edit(BreakingNews $breakingNews): View
    {
        abort_unless(auth()->user()->hasPermission('breaking.manage'), 403);

        return view('admin.breaking.form', [
            'item' => $breakingNews,
            'articles' => Article::query()->latest('updated_at')->limit(100)->get(),
        ]);
    }

    public function update(BreakingNewsRequest $request, BreakingNews $breakingNews): RedirectResponse
    {
        $breakingNews->update($this->payload($request));

        return redirect()->route('admin.breaking.index')->with('status', 'Breaking news updated.');
    }

    public function destroy(BreakingNews $breakingNews): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('breaking.manage'), 403);
        $breakingNews->delete();

        return redirect()->route('admin.breaking.index')->with('status', 'Breaking news deleted.');
    }

    private function payload(BreakingNewsRequest $request): array
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['article_id'] = $data['article_id'] ?: null;

        return $data;
    }
}
