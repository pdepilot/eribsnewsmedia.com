<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Advertisement;
use App\Models\Article;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsroomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_breaking_headlines_stay_visible_and_can_rotate(): void
    {
        BreakingNews::query()->create([
            'title' => 'First wire headline',
            'is_active' => true,
            'starts_at' => now()->subMinute(),
        ]);
        BreakingNews::query()->create([
            'title' => 'Second wire headline',
            'is_active' => true,
            'starts_at' => now()->subMinutes(2),
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('First wire headline', $html);
        $this->assertStringContainsString('Second wire headline', $html);
        $this->assertStringContainsString('data-breaking-next', $html);
        $this->assertMatchesRegularExpression('/data-breaking-item class="is-on"[^>]*>First wire headline/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-breaking-item class="is-on"[^>]*>Second wire headline/', $html);
        $this->assertStringContainsString('}, 5000);', $html);
        $this->assertStringNotContainsString('x-cloak', $html);

        $category = Category::query()->where('slug', 'news')->firstOrFail();
        Article::query()->create([
            'title' => 'Live breaking desk note',
            'slug' => 'live-breaking-desk-note',
            'body' => 'A filed note.',
            'category_id' => $category->id,
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'is_breaking' => true,
        ]);
        Article::query()->create([
            'title' => 'Draft breaking note',
            'slug' => 'draft-breaking-note',
            'body' => 'Not on the wire yet.',
            'category_id' => $category->id,
            'status' => ArticleStatus::Draft,
            'is_breaking' => true,
        ]);

        $page = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('Live breaking desk note', $page);
        $this->assertStringNotContainsString('Draft breaking note', $page);
    }

    public function test_public_pages_and_feeds_render(): void
    {
        $this->get('/')->assertOk()->assertSee('ERIBS Media')->assertSee('Breaking News')->assertSee('name="viewport"', false);
        $this->get(route('articles.latest'))->assertOk();
        $this->get(route('categories.show', 'news'))->assertOk()->assertSee('News');
        $this->get(route('categories.show', 'politics'))->assertOk();
        $this->get(route('pages.about'))->assertOk();
        $this->get(route('pages.contact'))->assertOk();
        $this->get(route('pages.privacy'))->assertOk();
        $this->get(route('pages.terms'))->assertOk();
        $this->get(route('search'))->assertOk();
        $this->get(route('feeds.sitemap'))->assertOk()->assertSee('<urlset', false);
        $this->get(route('feeds.news-sitemap'))->assertOk();
        $this->get(route('feeds.rss'))->assertOk()->assertSee('<rss', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /portal');
    }

    public function test_editorial_workflow_publish_search_and_seo(): void
    {
        $reporter = $this->userWithRole('reporter');
        $editor = $this->userWithRole('editor');
        $category = Category::query()->where('slug', 'news')->firstOrFail();

        $this->actingAs($reporter)->post(route('admin.articles.store'), [
            'title' => 'Council meets in Owerri',
            'body' => 'Reporters attended the meeting and filed this desk note.',
            'excerpt' => 'A short desk note from Owerri.',
            'category_id' => $category->id,
            'action' => 'submit',
        ])->assertRedirect();

        $article = Article::query()->where('title', 'Council meets in Owerri')->firstOrFail();
        $this->assertSame(ArticleStatus::PendingReview, $article->status);
        $this->get(route('articles.show', $article))->assertNotFound();

        $this->actingAs($reporter)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'action' => 'publish',
        ])->assertForbidden();

        $this->actingAs($editor)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'action' => 'approve',
        ])->assertRedirect();

        $this->actingAs($editor)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'action' => 'publish',
        ])->assertRedirect();

        $article->refresh();
        $this->assertSame(ArticleStatus::Published, $article->status);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Council meets in Owerri')
            ->assertSee('application/ld+json', false)
            ->assertSee('property="og:title"', false);

        $this->get('/')->assertSee('Council meets in Owerri');
        $this->get(route('categories.show', 'news'))->assertSee('Council meets in Owerri');
        $this->get(route('search', ['q' => 'Owerri']))->assertSee('Council meets in Owerri');
        $this->get(route('authors.show', $article->author))->assertOk()->assertSee('Council meets in Owerri');
        $this->get(route('feeds.sitemap'))->assertSee($article->slug);
        $this->get(route('feeds.rss'))->assertSee('Council meets in Owerri');
        $this->get(route('feeds.news-sitemap'))->assertSee('Council meets in Owerri');

        $this->assertDatabaseHas('article_views', ['article_id' => $article->id]);
    }

    public function test_scheduled_articles_publish_when_due(): void
    {
        $editor = $this->userWithRole('editor');
        $category = Category::query()->where('slug', 'politics')->firstOrFail();

        $this->actingAs($editor)->post(route('admin.articles.store'), [
            'title' => 'Scheduled desk note',
            'body' => 'This note is waiting for its publish time.',
            'category_id' => $category->id,
            'action' => 'submit',
        ])->assertRedirect();

        $article = Article::query()->where('title', 'Scheduled desk note')->firstOrFail();

        $this->actingAs($editor)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'action' => 'approve',
        ])->assertRedirect();

        $this->actingAs($editor)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'action' => 'schedule',
            'scheduled_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $article->refresh();
        $this->assertSame(ArticleStatus::Scheduled, $article->status);
        $this->get(route('articles.show', $article))->assertNotFound();

        $this->travelTo(now()->addHours(2));
        $this->artisan('articles:publish-scheduled')->assertSuccessful();

        $article->refresh();
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->get(route('articles.show', $article))->assertOk();
    }

    public function test_admin_authentication_media_and_placements(): void
    {
        $this->get('/portal')->assertRedirect('/portal/login');

        $this->post('/portal/login', [
            'email' => 'admin@eribs.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard');
        $this->get(route('admin.articles.create'))->assertOk();

        Storage::fake('public');

        $this->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('desk.jpg', 640, 480),
            'alt_text' => 'Desk photo',
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'alt_text' => 'Desk photo',
            'width' => 640,
            'height' => 480,
        ]);

        $this->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->create('shell.php', 20, 'application/x-php'),
        ])->assertSessionHasErrors('file');

        BreakingNews::query()->create([
            'title' => 'Desk flash',
            'is_active' => true,
        ]);
        Advertisement::query()->create([
            'name' => 'Homepage test unit',
            'placement' => 'homepage_top',
            'type' => 'html',
            'code' => '<!--ad-slot-test-->',
            'is_active' => true,
        ]);

        $this->get('/')->assertSee('Desk flash')->assertSee('ad-slot-test', false);

        $this->post(route('contact.store'), [
            'name' => 'Ada',
            'email' => 'reader@example.com',
            'subject' => 'Hello',
            'message' => 'A note for the desk.',
        ])->assertRedirect(route('pages.contact'));

        $this->assertDatabaseHas('contact_messages', ['email' => 'reader@example.com']);
    }

    public function test_seo_page_stores_an_uploaded_publisher_logo(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.seo.edit'))
            ->assertOk()
            ->assertSee('Publisher logo')
            ->assertSee('Choose image')
            ->assertDontSee('Publisher logo URL');

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'seo_form' => '1',
            'publisher_name' => 'ERIBS Media',
            'seo_title_suffix' => 'News',
            'publisher_logo' => UploadedFile::fake()->image('mark.png', 240, 80),
        ])->assertRedirect();

        $stored = app(Settings::class)->get('publisher_logo');
        $this->assertNotNull($stored);
        $this->assertStringStartsWith('media/', $stored);
        Storage::disk('public')->assertExists($stored);
        $this->assertStringContainsString('/storage/'.$stored, app(Settings::class)->logoUrl());
    }

    public function test_pulse_live_counts_a_public_read(): void
    {
        $admin = $this->userWithRole('admin');
        $editor = $this->userWithRole('editor');
        $category = Category::query()->where('slug', 'news')->firstOrFail();

        $this->actingAs($editor)->post(route('admin.articles.store'), [
            'title' => 'Pulse desk note',
            'body' => 'A note filed so the pulse desk can count a read.',
            'category_id' => $category->id,
            'action' => 'submit',
        ])->assertRedirect();

        $article = Article::query()->where('title', 'Pulse desk note')->firstOrFail();

        $this->actingAs($editor)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'action' => 'approve',
        ])->assertRedirect();

        $this->actingAs($editor)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'action' => 'publish',
        ])->assertRedirect();

        $this->get(route('articles.show', $article))->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSee('Just read')
            ->assertSee('Pulse desk note')
            ->assertSee(route('admin.analytics.live'), false);

        $this->actingAs($admin)
            ->getJson(route('admin.analytics.live'))
            ->assertOk()
            ->assertJsonPath('views_today', 1)
            ->assertJsonPath('latest.0.title', 'Pulse desk note');
    }

    public function test_setup_updates_login_details_and_assigns_roles(): void
    {
        $admin = $this->userWithRole('admin');
        $reporter = $this->userWithRole('reporter');
        $seededAdmin = User::query()->where('email', 'admin@eribs.test')->firstOrFail();
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
        $editorRole = Role::query()->where('slug', 'editor')->firstOrFail();
        $review = Permission::query()->where('slug', 'articles.review')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Portal login')
            ->assertSee('Assign roles')
            ->assertSee('Permissions')
            ->assertSee($admin->email);

        $this->actingAs($admin)->from(route('admin.settings.edit'))->put(route('admin.settings.account'), [
            'name' => 'Desk Chief',
            'email' => 'chief@eribs.test',
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('admin.settings.edit'));

        $admin->refresh();
        $this->assertSame('Desk Chief', $admin->name);
        $this->assertSame('chief@eribs.test', $admin->email);
        $this->assertTrue(Hash::check('new-password', $admin->password));

        $this->actingAs($admin)->from(route('admin.settings.edit'))->put(route('admin.settings.account'), [
            'name' => 'Desk Chief',
            'email' => 'chief@eribs.test',
            'current_password' => 'wrong-password',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($admin)->put(route('admin.users.roles'), [
            'roles' => [
                $admin->id => $adminRole->id,
                $seededAdmin->id => $adminRole->id,
                $reporter->id => $editorRole->id,
            ],
        ])->assertRedirect();

        $this->assertTrue($reporter->fresh()->hasRole('editor'));

        $this->actingAs($admin)->put(route('admin.roles.update', $editorRole), [
            'permissions' => [$review->id],
        ])->assertRedirect();

        $this->assertTrue($editorRole->fresh()->permissions()->where('slug', 'articles.review')->exists());
        $this->assertFalse($editorRole->fresh()->permissions()->where('slug', 'articles.publish')->exists());

        $this->actingAs($admin)->from(route('admin.settings.edit'))->put(route('admin.users.roles'), [
            'roles' => [
                $admin->id => $editorRole->id,
                $seededAdmin->id => $editorRole->id,
                $reporter->id => $editorRole->id,
            ],
        ])->assertSessionHasErrors('roles');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('slug', $role)->firstOrFail());

        return $user;
    }
}
