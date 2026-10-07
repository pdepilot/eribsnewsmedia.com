<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\Settings;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class FeedController extends Controller
{
    public function sitemap(Settings $settings): Response
    {
        $xml = Cache::remember('feed.sitemap', 300, function () use ($settings) {
            return view('feeds.sitemap', [
                'articles' => Article::query()->published()->latest('updated_at')->limit(5000)->get(),
                'pages' => $this->pages(),
                'siteName' => $settings->get('site_name', 'ERIBS Media'),
            ])->render();
        });

        return $this->xml($xml);
    }

    public function newsSitemap(Settings $settings): Response
    {
        $xml = Cache::remember('feed.news', 300, function () use ($settings) {
            return view('feeds.news-sitemap', [
                'articles' => Article::query()
                    ->published()
                    ->with('category')
                    ->where('published_at', '>=', now()->subDays(2))
                    ->latest('published_at')
                    ->limit(1000)
                    ->get(),
                'siteName' => $settings->get('site_name', 'ERIBS Media'),
            ])->render();
        });

        return $this->xml($xml);
    }

    public function rss(Settings $settings): Response
    {
        $xml = Cache::remember('feed.rss', 300, function () use ($settings) {
            return view('feeds.rss', [
                'articles' => Article::query()->published()->with(['author', 'category'])->latest('published_at')->limit(30)->get(),
                'siteName' => $settings->get('site_name', 'ERIBS Media'),
                'description' => (string) $settings->get('seo_default_description', ''),
            ])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    private function xml(string $xml): Response
    {
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function pages(): array
    {
        return [
            route('home'),
            route('articles.latest'),
            route('pages.about'),
            route('pages.contact'),
            route('pages.privacy'),
            route('pages.terms'),
        ];
    }
}
