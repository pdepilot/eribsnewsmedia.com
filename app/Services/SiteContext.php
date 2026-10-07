<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\Article;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\Comment;

class SiteContext
{
    private ?array $payload = null;

    public function __construct(private Settings $settings) {}

    public function data(): array
    {
        if ($this->payload !== null) {
            return $this->payload;
        }

        $settings = $this->settings->all();

        return $this->payload = [
            'siteSettings' => $settings,
            'siteName' => $settings['site_name'] ?? 'ERIBS Media',
            'navCategories' => Category::query()->where('show_in_nav', true)->orderBy('sort_order')->orderBy('name')->get(),
            'recentPosts' => Article::query()->published()->latest('published_at')->limit(5)->get(),
            'recentComments' => Comment::query()->with('article')->latest()->limit(5)->get(),
            'breakingItems' => $this->breakingItems(),
            'ads' => Advertisement::query()->active()->get()->groupBy('placement'),
        ];
    }

    private function breakingItems(): \Illuminate\Support\Collection
    {
        $articles = Article::query()
            ->published()
            ->where('is_breaking', true)
            ->latest('published_at')
            ->latest('id')
            ->limit(12)
            ->get();

        $shown = $articles->pluck('id');

        $fromArticles = $articles->map(fn (Article $article) => (object) [
            'title' => $article->title,
            'url' => route('articles.show', $article),
        ]);

        $fromWire = BreakingNews::query()
            ->active()
            ->with('article')
            ->latest('starts_at')
            ->limit(12)
            ->get()
            ->reject(fn (BreakingNews $item) => $item->article_id && $shown->contains($item->article_id))
            ->map(fn (BreakingNews $item) => (object) [
                'title' => $item->title,
                'url' => $item->publicUrl(),
            ]);

        return $fromArticles->concat($fromWire)->take(12)->values();
    }
}
