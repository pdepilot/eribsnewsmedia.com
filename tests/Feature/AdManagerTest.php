<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\AdCampaign;
use App\Models\AdCreative;
use App\Models\AdImpression;
use App\Models\AdNetwork;
use App\Models\AdPlacement;
use App\Models\AdUnit;
use App\Models\AdsTxtEntry;
use App\Models\Advertiser;
use App\Models\Article;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_monetag_service_worker_matches_the_supplied_file(): void
    {
        $body = file_get_contents(public_path('sw.js'));

        $this->assertIsString($body);
        $this->assertStringContainsString('"domain": "3nbf4.com"', $body);
        $this->assertStringContainsString('"zoneId": 11975535', $body);
        $this->assertStringContainsString("importScripts('https://3nbf4.com/act/files/service-worker.min.js?r=sw')", $body);
    }

    public function test_monetag_verification_meta_is_printed_unchanged(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="monetag" content="2b1599a262976829a3dc2a5889acbe2d">', false);
    }

    public function test_admin_can_open_the_advertising_dashboard_and_a_reporter_cannot(): void
    {
        $admin = User::query()->where('email', 'admin@eribs.test')->firstOrFail();
        $reporter = User::factory()->create();
        $reporter->roles()->attach(Role::query()->where('slug', 'reporter')->firstOrFail());

        $this->actingAs($admin)->get(route('admin.advertising.index'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee("Today's impressions");

        $this->actingAs($admin)->get(route('admin.advertising.analytics'))->assertOk()->assertSee('Analytics');

        $this->actingAs($reporter)->get(route('admin.advertising.index'))->assertForbidden();
        $this->actingAs($reporter)->post(route('admin.advertising.networks.store'), [
            'name' => 'Blocked',
            'type' => 'custom',
        ])->assertForbidden();
    }

    public function test_admin_can_create_a_network_placement_and_unit(): void
    {
        $admin = User::query()->where('email', 'admin@eribs.test')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.advertising.networks.store'), [
            'name' => 'House custom',
            'type' => 'custom',
            'enabled' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('ad_networks', ['slug' => 'house-custom', 'publisher_id' => null]);

        $this->actingAs($admin)->post(route('admin.advertising.placements.store'), [
            'name' => 'Story end',
            'device' => 'all',
            'enabled' => '1',
        ])->assertRedirect();

        $network = AdNetwork::query()->where('slug', 'house-custom')->firstOrFail();
        $placement = AdPlacement::query()->where('slug', 'story-end')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.advertising.units.store'), [
            'name' => 'House tag',
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'format' => 'html',
            'device' => 'all',
            'markup' => '<!--HOUSE-->',
            'enabled' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('ad_units', ['name' => 'House tag', 'markup' => '<!--HOUSE-->']);
    }

    public function test_units_honor_status_schedule_page_device_priority_and_weight(): void
    {
        $placement = AdPlacement::query()->where('slug', 'homepage_bottom')->firstOrFail();
        $network = AdNetwork::query()->create([
            'name' => 'Custom desk',
            'slug' => 'custom-desk',
            'type' => 'custom',
            'enabled' => true,
        ]);

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Off',
            'markup' => '<!--OFF-->',
            'device' => 'all',
            'page_target' => 'all',
            'enabled' => false,
            'priority' => 50,
        ]);

        $this->get('/')->assertDontSee('<!--OFF-->', false);

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Future',
            'markup' => '<!--FUTURE-UNIT-->',
            'device' => 'all',
            'page_target' => 'homepage',
            'enabled' => true,
            'priority' => 40,
            'starts_at' => now()->addDay(),
        ]);

        $this->get('/')->assertDontSee('<!--FUTURE-UNIT-->', false);

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Expired',
            'markup' => '<!--EXPIRED-UNIT-->',
            'device' => 'all',
            'page_target' => 'homepage',
            'enabled' => true,
            'priority' => 40,
            'ends_at' => now()->subMinute(),
        ]);

        $this->get('/')->assertDontSee('<!--EXPIRED-UNIT-->', false);

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Article only',
            'markup' => '<!--ARTICLE-ONLY-->',
            'device' => 'all',
            'page_target' => 'article',
            'enabled' => true,
            'priority' => 30,
        ]);

        $this->get('/')->assertDontSee('<!--ARTICLE-ONLY-->', false);

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Mobile only',
            'markup' => '<!--MOBILE-ONLY-->',
            'device' => 'mobile',
            'page_target' => 'homepage',
            'enabled' => true,
            'priority' => 20,
        ]);

        $this->get('/')->assertDontSee('<!--MOBILE-ONLY-->', false);
        $this->withHeader('Sec-CH-UA-Mobile', '?1')->get('/')->assertSee('<!--MOBILE-ONLY-->', false);
        $this->withHeader('Sec-CH-UA-Mobile', '?0');

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Low',
            'markup' => '<!--LOW-->',
            'device' => 'desktop',
            'page_target' => 'homepage',
            'enabled' => true,
            'priority' => 1,
            'weight' => 100,
        ]);

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'High',
            'markup' => '<!--HIGH-->',
            'device' => 'desktop',
            'page_target' => 'homepage',
            'enabled' => true,
            'priority' => 9,
            'weight' => 1,
        ]);

        $this->get('/')->assertSee('<!--HIGH-->', false)->assertDontSee('<!--LOW-->', false);
    }

    public function test_weight_roll_selects_the_heavier_unit_and_fallback_renders_nothing_otherwise(): void
    {
        $placement = AdPlacement::query()->where('slug', 'sidebar_top')->firstOrFail();
        $network = AdNetwork::query()->create([
            'name' => 'Rotate',
            'slug' => 'rotate',
            'type' => 'custom',
            'enabled' => true,
        ]);

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Light',
            'markup' => '<!--LIGHT-->',
            'device' => 'all',
            'page_target' => 'all',
            'enabled' => true,
            'priority' => 3,
            'weight' => 1,
            'fallback_code' => '<!--FALLBACK-->',
        ]);

        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Heavy',
            'markup' => '<!--HEAVY-->',
            'device' => 'all',
            'page_target' => 'all',
            'enabled' => true,
            'priority' => 3,
            'weight' => 9,
        ]);

        $this->get('/')->assertSee('<!--LIGHT-->', false)->assertDontSee('<!--HEAVY-->', false);
        $this->withHeader('X-Ad-Roll', '5')->get('/')->assertSee('<!--HEAVY-->', false)->assertDontSee('<!--LIGHT-->', false);

        AdUnit::query()->where('ad_placement_id', $placement->id)->update(['enabled' => false, 'markup' => null]);
        AdUnit::query()->where('name', 'Light')->update(['enabled' => true, 'markup' => null, 'fallback_code' => '<!--FALLBACK-->']);

        $this->get('/')->assertSee('<!--FALLBACK-->', false);

        AdUnit::query()->where('name', 'Light')->update(['enabled' => false]);
        $this->get('/')->assertDontSee('<!--FALLBACK-->', false)->assertDontSee('<aside class="ad-slot ad-slot-sidebar_top"', false);
    }

    public function test_direct_creative_schedule_analytics_and_ads_txt(): void
    {
        $placement = AdPlacement::query()->where('slug', 'homepage_middle')->firstOrFail();
        $advertiser = Advertiser::query()->create(['name' => 'Sponsor', 'status' => 'active']);
        $creative = AdCreative::query()->create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Card',
            'type' => 'html',
            'html' => '<!--SPONSOR-->',
            'status' => 'active',
        ]);
        $campaign = AdCampaign::query()->create([
            'advertiser_id' => $advertiser->id,
            'ad_placement_id' => $placement->id,
            'creative_id' => $creative->id,
            'name' => 'October',
            'status' => 'active',
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
            'priority' => 2,
            'weight' => 100,
            'click_url' => 'https://example.com/sponsor',
        ]);

        $this->get('/')->assertSee('<!--SPONSOR-->', false);
        $this->post(route('advertising.impression', $campaign))->assertNoContent();
        $impression = AdImpression::query()->first();
        $this->assertNotNull($impression);
        $this->assertSame(64, strlen((string) $impression->ip_hash));
        $this->assertNotSame('127.0.0.1', $impression->ip_hash);
        $this->assertSame($placement->id, $impression->ad_placement_id);

        $admin = User::query()->where('email', 'admin@eribs.test')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.advertising.analytics', ['range' => 'today']))
            ->assertOk()
            ->assertSee('Impressions')
            ->assertSee('>1<', false);

        AdsTxtEntry::query()->create([
            'advertising_system' => 'google.com',
            'publisher_account_id' => 'pub-0000000000000001',
            'relationship' => 'DIRECT',
            'certification_authority_id' => 'f08c47fec0942fa0',
            'status' => true,
        ]);

        $this->get('/ads.txt')->assertOk()->assertSee('google.com, pub-0000000000000001, DIRECT, f08c47fec0942fa0', false);

        $campaign->update(['end_at' => now()->subMinute(), 'status' => 'active']);
        $this->get('/')->assertDontSee('<!--SPONSOR-->', false);
    }

    public function test_invalid_creative_upload_is_rejected_and_existing_pages_still_render(): void
    {
        Storage::fake('public');
        $admin = User::query()->where('email', 'admin@eribs.test')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.advertising.creatives.store'), [
            'name' => 'Bad file',
            'type' => 'image',
            'image' => UploadedFile::fake()->create('shell.php', 20, 'application/x-php'),
        ])->assertSessionHasErrors('image');

        $network = AdNetwork::query()->create([
            'name' => 'Paragraph',
            'slug' => 'paragraph-net',
            'type' => 'custom',
            'enabled' => true,
        ]);
        $paragraph = AdPlacement::query()->where('slug', 'article_after_paragraph_2')->firstOrFail();
        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $paragraph->id,
            'name' => 'After two',
            'markup' => '<!--PARA-->',
            'device' => 'all',
            'page_target' => 'article',
            'enabled' => true,
            'priority' => 1,
        ]);

        $bottom = AdPlacement::query()->where('slug', 'homepage_bottom')->firstOrFail();
        AdUnit::query()->create([
            'ad_network_id' => $network->id,
            'ad_placement_id' => $bottom->id,
            'name' => 'Bottom',
            'markup' => '<!--BOTTOM-->',
            'device' => 'all',
            'page_target' => 'homepage',
            'enabled' => true,
            'priority' => 1,
        ]);

        $category = Category::query()->where('slug', 'news')->firstOrFail();
        $article = Article::query()->create([
            'title' => 'Picture story',
            'slug' => 'picture-story',
            'body' => "First paragraph.\n\nSecond paragraph.\n\nThird paragraph.",
            'category_id' => $category->id,
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'featured_image' => 'media/2026/10/example.jpg',
        ]);

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<div class="article-body">(.*?)<\/div>/s', $html, $body));
        $second = strpos($body[1], 'Second paragraph');
        $marker = strpos($body[1], '<!--PARA-->');
        $third = strpos($body[1], 'Third paragraph');
        $this->assertNotFalse($second);
        $this->assertNotFalse($marker);
        $this->assertNotFalse($third);
        $this->assertTrue($second < $marker && $marker < $third);
        $this->assertStringNotContainsString('<!--PARA-->', (string) $article->fresh()->body);
        $this->assertStringContainsString('/storage/media/2026/10/example.jpg', $html);

        $home = $this->withHeader('Sec-CH-UA-Mobile', '?1')->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<aside class="ad-slot ad-slot-homepage_bottom[^"]*"[^>]*>.*?<!--BOTTOM-->.*?<\/aside>/s', $home);
        preg_match('/<aside class="ad-slot ad-slot-homepage_bottom[^"]*"[^>]*>.*?<\/aside>/s', $home, $slot);
        $this->assertStringNotContainsString('position:fixed', $slot[0]);
        $this->assertStringNotContainsString('100vw', $slot[0]);
    }
}
