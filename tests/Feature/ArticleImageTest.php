<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_uploaded_article_image_is_stored_and_rendered_on_the_request_host(): void
    {
        config(['app.url' => 'http://localhost:8000']);
        $admin = $this->userWithRole('admin');
        $category = Category::query()->where('slug', 'news')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Desk photo story',
            'body' => 'A published note with a picture.',
            'category_id' => $category->id,
            'featured_image' => UploadedFile::fake()->image('desk.jpg', 640, 480),
            'featured_image_alt' => 'The news desk',
            'action' => 'save',
        ])->assertRedirect();

        $article = Article::query()->where('title', 'Desk photo story')->firstOrFail();
        $this->assertNotNull($article->featured_image);
        $this->assertStringStartsWith('media/', $article->featured_image);
        $this->assertStringNotContainsString('localhost', $article->featured_image);
        Storage::disk('public')->assertExists($article->featured_image);

        $article->update([
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $imageUrl = 'http://127.0.0.1:8000/storage/'.$article->featured_image;

        $this->get('http://127.0.0.1:8000'.route('articles.show', $article, false))
            ->assertOk()
            ->assertSee($imageUrl, false)
            ->assertSee('property="og:image" content="'.$imageUrl.'"', false)
            ->assertDontSee('http://localhost:8000/storage/', false);

        $this->actingAs($admin)
            ->get('http://127.0.0.1:8000'.route('admin.articles.index', absolute: false))
            ->assertOk()
            ->assertSee($imageUrl, false);

        $this->get('http://127.0.0.1:8000/')
            ->assertOk()
            ->assertSee($imageUrl, false);
    }

    public function test_webp_upload_replaces_the_previous_image(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::query()->where('slug', 'news')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Picture swap',
            'body' => 'The first picture is replaced.',
            'category_id' => $category->id,
            'featured_image' => UploadedFile::fake()->image('first.jpg', 320, 200),
            'action' => 'save',
        ])->assertRedirect();

        $article = Article::query()->where('title', 'Picture swap')->firstOrFail();
        $firstPath = $article->featured_image;

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'featured_image' => UploadedFile::fake()->image('second.webp', 320, 200),
            'action' => 'save',
        ])->assertRedirect();

        $article->refresh();
        $this->assertNotSame($firstPath, $article->featured_image);
        $this->assertStringEndsWith('.webp', $article->featured_image);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($article->featured_image);
    }

    public function test_article_without_an_image_renders_and_removal_clears_the_file(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::query()->where('slug', 'news')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Words only',
            'body' => 'This story has no picture.',
            'category_id' => $category->id,
            'action' => 'save',
        ])->assertRedirect();

        $article = Article::query()->where('title', 'Words only')->firstOrFail();
        $article->update(['status' => ArticleStatus::Published, 'published_at' => now()]);
        $this->assertNull($article->featuredImageUrl());
        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertDontSee('article-figure', false);

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'featured_image' => UploadedFile::fake()->image('gone.jpg', 200, 120),
            'action' => 'save',
        ])->assertRedirect();

        $article->refresh();
        $path = $article->featured_image;
        $this->assertNotNull($path);

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'category_id' => $category->id,
            'remove_featured_image' => '1',
            'action' => 'save',
        ])->assertRedirect();

        $article->refresh();
        $this->assertNull($article->featured_image);
        $this->assertNull($article->featuredImageUrl());
        Storage::disk('public')->assertMissing($path);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('slug', $role)->firstOrFail());

        return $user;
    }
}
