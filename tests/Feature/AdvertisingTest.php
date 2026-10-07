<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\AdCampaign;
use App\Models\AdCreative;
use App\Models\AdNetwork;
use App\Models\AdPlacement;
use App\Models\AdUnit;
use App\Models\Advertisement;
use App\Models\Advertiser;
use App\Models\Article;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_ads_txt_is_empty_until_a_real_line_is_saved(): void
    {
        $this->get('/ads.txt')
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertDontSee('ca-pub');

        $admin = User::query()->where('email', 'admin@eribs.test')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.advertising.ads-txt.update'), [
            'lines' => "not a record\n",
        ])->assertSessionHasErrors('lines');

        $this->actingAs($admin)->put(route('admin.advertising.ads-txt.update'), [
            'lines' => "google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0\n",
        ])->assertRedirect(route('admin.advertising.ads-txt'));

        $this->get('/ads.txt')
            ->assertOk()
            ->assertSee('google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0', false);
    }

    public function test_direct_campaigns_follow_their_schedule_and_record_traffic(): void
    {
        $campaign = $this->campaign('active', now()->addDay(), now()->addDays(2), 'FUTURE-MARKER');

        $this->get('/')->assertDontSee('FUTURE-MARKER');
        $this->assertSame('scheduled', $campaign->fresh()->status);

        $campaign->update([
            'status' => 'active',
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
        ]);

        $this->get('/')->assertSee('FUTURE-MARKER', false)->assertSee('Sponsored');
        $this->post(route('advertising.impression', $campaign))->assertNoContent();
        $this->assertSame(1, $campaign->fresh()->impressions);
        $this->get(route('advertising.click', $campaign))->assertRedirect('https://example.com/offer');
        $this->assertSame(1, $campaign->fresh()->clicks);

        $campaign->update([
            'status' => 'active',
            'start_at' => now()->subDays(2),
            'end_at' => now()->subMinute(),
        ]);

        $this->get('/')->assertDontSee('FUTURE-MARKER');
        $this->assertSame('ended', $campaign->fresh()->status);
        $this->post(route('advertising.impression', $campaign))->assertNotFound();
    }

    public function test_slot_falls_back_from_direct_to_third_party_to_adsense(): void
    {
        $placement = AdPlacement::query()->where('slug', 'homepage_bottom')->firstOrFail();
        $adsense = AdNetwork::query()->where('slug', 'google-adsense')->firstOrFail();
        $adsense->update(['enabled' => true, 'publisher_id' => 'ca-pub-0000000000000']);
        app(Settings::class)->setMany(['adsense_enabled' => '1']);

        AdUnit::query()->create([
            'ad_network_id' => $adsense->id,
            'ad_placement_id' => $placement->id,
            'name' => 'Manual placeholder',
            'ad_client' => 'ca-pub-0000000000000',
            'ad_slot' => '1234567890',
            'format' => 'auto',
            'responsive' => true,
            'device' => 'all',
            'enabled' => true,
        ]);

        $third = AdNetwork::query()->create([
            'name' => 'House network',
            'slug' => 'house-network',
            'type' => 'third_party',
            'enabled' => true,
            'priority' => 20,
        ]);

        AdUnit::query()->create([
            'ad_network_id' => $third->id,
            'ad_placement_id' => $placement->id,
            'name' => 'House tag',
            'device' => 'all',
            'enabled' => true,
            'markup' => '<!--THIRD-MARKER-->',
        ]);

        $campaign = $this->campaign('active', now()->subHour(), now()->addDay(), 'DIRECT-MARKER');

        $this->get('/')
            ->assertSee('DIRECT-MARKER', false)
            ->assertDontSee('THIRD-MARKER', false)
            ->assertDontSee('1234567890', false);

        $campaign->update(['status' => 'paused']);

        $this->get('/')
            ->assertDontSee('DIRECT-MARKER', false)
            ->assertSee('THIRD-MARKER', false)
            ->assertDontSee('1234567890', false);

        $third->update(['enabled' => false]);

        $this->get('/')
            ->assertSee('data-ad-slot="1234567890"', false)
            ->assertSee('pagead2.googlesyndication.com', false)
            ->assertDontSee('THIRD-MARKER', false);

        $this->get('/portal/login')->assertDontSee('pagead2.googlesyndication.com', false);

        $admin = User::query()->where('email', 'admin@eribs.test')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.advertising.index'))
            ->assertOk()
            ->assertSee('Google AdSense revenue is not shown')
            ->assertDontSee('pagead2.googlesyndication.com', false);
    }

    public function test_advertising_can_be_switched_off(): void
    {
        app(Settings::class)->setMany(['advertising_enabled' => '0']);

        Advertisement::query()->create([
            'name' => 'Hidden unit',
            'placement' => 'homepage_top',
            'type' => 'html',
            'code' => '<!--ad-slot-test-->',
            'is_active' => true,
        ]);

        $this->get('/')->assertDontSee('ad-slot-test', false);

        $category = \App\Models\Category::query()->where('slug', 'news')->firstOrFail();
        $article = Article::query()->create([
            'title' => 'Desk note',
            'slug' => 'desk-note',
            'body' => 'A filed note.',
            'category_id' => $category->id,
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $this->get(route('articles.show', $article))->assertOk()->assertDontSee('pagead2.googlesyndication.com', false);
        $this->get(route('categories.show', $category))->assertOk()->assertDontSee('pagead2.googlesyndication.com', false);
    }

    private function campaign(string $status, $start, $end, string $html): AdCampaign
    {
        $placement = AdPlacement::query()->where('slug', 'homepage_bottom')->firstOrFail();
        $advertiser = Advertiser::query()->create([
            'name' => 'Desk sponsor',
            'status' => 'active',
        ]);
        $creative = AdCreative::query()->create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Desk creative',
            'type' => 'html',
            'html' => $html,
        ]);

        return AdCampaign::query()->create([
            'advertiser_id' => $advertiser->id,
            'ad_placement_id' => $placement->id,
            'creative_id' => $creative->id,
            'name' => 'Desk campaign',
            'status' => $status,
            'start_at' => $start,
            'end_at' => $end,
            'priority' => 10,
            'click_url' => 'https://example.com/offer',
        ]);
    }
}
