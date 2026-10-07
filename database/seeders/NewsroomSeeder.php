<?php

namespace Database\Seeders;

use App\Models\AdNetwork;
use App\Models\AdPlacement;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsroomSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'articles.view' => 'View articles',
            'articles.create' => 'Create articles',
            'articles.update' => 'Update articles',
            'articles.delete' => 'Delete articles',
            'articles.submit' => 'Submit articles',
            'articles.review' => 'Review articles',
            'articles.publish' => 'Publish articles',
            'categories.manage' => 'Manage categories',
            'tags.manage' => 'Manage tags',
            'authors.manage' => 'Manage authors',
            'media.manage' => 'Manage media',
            'breaking.manage' => 'Manage breaking news',
            'ads.manage' => 'Manage advertisements',
            'users.manage' => 'Manage users',
            'roles.manage' => 'Manage roles',
            'settings.manage' => 'Manage settings',
            'seo.manage' => 'Manage SEO',
            'analytics.view' => 'View analytics',
            'messages.view' => 'View messages',
        ];

        foreach ($permissions as $slug => $name) {
            Permission::query()->updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $roles = collect([
            'admin' => 'Administrator',
            'editor' => 'Editor',
            'reporter' => 'Reporter',
        ])->map(fn (string $name, string $slug) => Role::query()->updateOrCreate(['slug' => $slug], ['name' => $name]));

        $all = Permission::query()->pluck('id', 'slug');
        $roles['admin']->permissions()->sync($all->values());

        $editor = [
            'articles.view', 'articles.create', 'articles.update', 'articles.delete', 'articles.submit',
            'articles.review', 'articles.publish', 'categories.manage', 'tags.manage', 'authors.manage',
            'media.manage', 'breaking.manage', 'ads.manage', 'analytics.view', 'messages.view',
        ];
        $roles['editor']->permissions()->sync($all->only($editor)->values());

        $reporter = ['articles.view', 'articles.create', 'articles.update', 'articles.submit'];
        $roles['reporter']->permissions()->sync($all->only($reporter)->values());

        Category::query()->updateOrCreate(['slug' => 'news'], [
            'name' => 'News',
            'sort_order' => 1,
            'show_on_home' => true,
            'show_in_nav' => false,
        ]);
        Category::query()->updateOrCreate(['slug' => 'politics'], [
            'name' => 'Politics',
            'sort_order' => 2,
            'show_on_home' => true,
            'show_in_nav' => true,
        ]);
        Category::query()->updateOrCreate(['slug' => 'sport'], [
            'name' => 'Sport',
            'sort_order' => 3,
            'show_on_home' => true,
            'show_in_nav' => true,
        ]);

        $settings = [
            'site_name' => 'ERIBS Media',
            'tagline' => '',
            'copyright' => 'ERIBS Media',
            'contact_email' => '',
            'social_facebook' => '',
            'social_x' => '',
            'social_instagram' => '',
            'social_youtube' => '',
            'social_pinterest' => '',
            'seo_title_suffix' => '',
            'seo_default_description' => '',
            'publisher_name' => 'ERIBS Media',
            'publisher_logo' => '',
            'twitter_handle' => '',
            'page_about' => '',
            'page_privacy' => '',
            'page_terms' => '',
            'analytics_id' => 'GT-5M8TBNT',
            'analytics_enabled' => '0',
            'advertising_enabled' => '1',
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->seedAdvertising();

        if (! app()->environment('production')) {
            $admin = User::query()->updateOrCreate(
                ['email' => 'admin@eribs.test'],
                ['name' => 'ERIBS Desk', 'password' => 'password'],
            );
            $admin->roles()->sync([$roles['admin']->id]);
        }
    }

    private function seedAdvertising(): void
    {
        $placements = [
            ['header', 'Header', 'all', 10],
            ['homepage_top', 'Homepage top', 'all', 20],
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
            ['footer', 'Footer', 'all', 80],
            ['mobile', 'Mobile', 'mobile', 90],
            ['mobile_anchor', 'Mobile anchor', 'mobile', 91],
        ];

        foreach ($placements as [$slug, $name, $device, $sort]) {
            AdPlacement::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
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
    }
}
