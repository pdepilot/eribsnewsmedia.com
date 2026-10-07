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
            'breakingItems' => BreakingNews::query()->active()->with('article')->latest('starts_at')->limit(12)->get(),
            'ads' => Advertisement::query()->active()->get()->groupBy('placement'),
        ];
    }
}
