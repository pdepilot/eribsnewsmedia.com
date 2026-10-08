<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleEngagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['app.url' => 'https://news.example.test']);
    }

    public function test_a_visit_counts_once_and_the_page_shows_it(): void
    {
        $article = $this->article('Desk view note');

        $this->assertSame(0, (int) $article->views_count);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('1 view', false);

        $article->refresh();
        $this->assertSame(1, (int) $article->views_count);
        $this->assertSame(1, $article->views()->count());

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('1 view', false);

        $article->refresh();
        $this->assertSame(1, (int) $article->views_count);
        $this->assertSame(1, $article->views()->count());
    }

    public function test_article_page_has_one_absolute_canonical_url(): void
    {
        $article = $this->article('Canonical desk note');
        $canonical = 'https://news.example.test/article/'.$article->slug;

        $this->assertSame($canonical, $article->canonicalUrl());

        $page = $this->get(route('articles.show', $article).'?utm_source=wire')
            ->assertOk();

        $html = $page->getContent();
        $this->assertSame(1, substr_count($html, 'rel="canonical"'));
        $page->assertSee('rel="canonical" href="'.$canonical.'"', false);
        $page->assertSee('property="og:url" content="'.$canonical.'"', false);
        $this->assertStringNotContainsString('utm_source', $this->canonicalTag($html));
        $this->assertStringNotContainsString('?', $this->canonicalTag($html));
    }

    public function test_production_canonical_uses_https_from_the_configured_url(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'http://desk.example.test']);

        $article = $this->article('Secure desk note');

        $this->assertSame(
            'https://desk.example.test/article/'.$article->slug,
            $article->canonicalUrl(),
        );
    }

    public function test_share_links_use_the_canonical_url(): void
    {
        $article = $this->article('Share desk note');
        $canonical = $article->canonicalUrl();

        $page = $this->get(route('articles.show', $article))->assertOk();
        $page->assertSee('https://web.whatsapp.com/send?text=', false);
        $page->assertSee(rawurlencode($article->title), false);
        $page->assertSee(rawurlencode($canonical), false);
        $page->assertSee('https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($canonical), false);
        $page->assertSee('https://twitter.com/intent/tweet?url='.rawurlencode($canonical), false);
        $page->assertSee('data-url="'.$canonical.'"', false);
        $page->assertSee('Check out this story from ERIBS Media:', false);

        $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)')
            ->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('https://api.whatsapp.com/send?text=', false)
            ->assertDontSee('https://web.whatsapp.com/send?text=', false);
    }

    public function test_most_viewed_scopes_order_articles(): void
    {
        $quiet = $this->article('Quiet desk note');
        $loud = $this->article('Loud desk note');

        $this->get(route('articles.show', $loud))->assertOk();

        $this->assertSame($loud->id, Article::query()->published()->mostViewed()->first()->id);
        $this->assertSame($loud->id, Article::query()->published()->mostViewedToday()->first()->id);
        $this->assertSame($loud->id, Article::query()->published()->mostViewedThisWeek()->first()->id);
        $this->assertSame($loud->id, Article::query()->published()->mostViewedThisMonth()->first()->id);
        $this->assertNotSame($quiet->id, Article::query()->published()->mostViewed()->first()->id);
    }

    private function article(string $title): Article
    {
        return Article::query()->create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'body' => 'A filed note.',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'canonical_url' => 'https://tracking.example/story?utm_source=wire',
        ]);
    }

    private function canonicalTag(string $html): string
    {
        preg_match('/<link rel="canonical" href="[^"]*">/', $html, $match);

        return $match[0] ?? '';
    }
}
