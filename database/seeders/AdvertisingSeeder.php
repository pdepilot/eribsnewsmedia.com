<?php

namespace Database\Seeders;

use App\Models\AdNetwork;
use App\Models\AdPlacement;
use Illuminate\Database\Seeder;

class AdvertisingSeeder extends Seeder
{
    public function run(): void
    {
        $placements = [
            ['header', 'Header', 'all', 10],
            ['homepage_top', 'Homepage top', 'all', 20],
            ['homepage_after_featured', 'Homepage after featured', 'all', 25],
            ['homepage_after_hero', 'Homepage after hero', 'all', 30],
            ['homepage_middle', 'Homepage middle', 'all', 40],
            ['homepage_bottom', 'Homepage bottom', 'all', 50],
            ['sidebar', 'Sidebar', 'all', 60],
            ['sidebar_top', 'Sidebar top', 'all', 61],
            ['sidebar_middle', 'Sidebar middle', 'all', 62],
            ['sidebar_bottom', 'Sidebar bottom', 'all', 63],
            ['article_top', 'Article top', 'all', 70],
            ['article_after_intro', 'Article after intro', 'all', 71],
            ['article_middle', 'Article middle', 'all', 72],
            ['article_bottom', 'Article bottom', 'all', 73],
            ['article_after_paragraph_2', 'Article after paragraph 2', 'all', 74],
            ['article_after_paragraph_4', 'Article after paragraph 4', 'all', 75],
            ['footer', 'Footer', 'all', 80],
            ['mobile', 'Mobile', 'mobile', 90],
            ['mobile_anchor', 'Mobile anchor', 'mobile', 91],
            ['mobile_sticky', 'Mobile sticky', 'mobile', 92],
        ];

        foreach ($placements as [$slug, $name, $device, $sort]) {
            AdPlacement::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'location' => $slug,
                'device' => $device,
                'enabled' => true,
                'sort_order' => $sort,
            ]);
        }

        AdNetwork::query()->firstOrCreate(['slug' => 'google-adsense'], [
            'name' => 'Google AdSense',
            'type' => 'adsense',
            'publisher_id' => null,
            'enabled' => false,
            'priority' => 10,
            'configuration' => ['auto_ads' => false],
        ]);

        AdNetwork::query()->firstOrCreate(['slug' => 'google-ad-manager'], [
            'name' => 'Google Ad Manager',
            'type' => 'ad_manager',
            'publisher_id' => null,
            'enabled' => false,
            'priority' => 0,
            'configuration' => ['status' => 'reserved'],
        ]);

        AdNetwork::query()->firstOrCreate(['slug' => 'monetag'], [
            'name' => 'Monetag',
            'type' => 'monetag',
            'publisher_id' => null,
            'enabled' => false,
            'notes' => 'Inactive demo. The verification meta is printed in the page head. Paste a Monetag ad tag into an ad unit before turning this on.',
            'configuration' => [
                'head' => '<meta name="monetag" content="2b1599a262976829a3dc2a5889acbe2d">',
            ],
        ]);

        foreach ([
            'adsterra' => 'Adsterra',
            'medianet' => 'Media.net',
        ] as $slug => $name) {
            AdNetwork::query()->firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'type' => $slug === 'medianet' ? 'medianet' : $slug,
                'publisher_id' => null,
                'enabled' => false,
                'notes' => 'Inactive demo. No publisher ID is stored. Paste the network code into an ad unit when the owner supplies it.',
            ]);
        }
    }
}
