<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Media;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ArticleService
{
    public function __construct(private MediaLibrary $media) {}

    public function create(User $user, array $data, ?UploadedFile $image = null): Article
    {
        return DB::transaction(function () use ($user, $data, $image) {
            $article = Article::query()->create($this->attributes($user, $data));
            $this->syncTags($article, $data['tags'] ?? null);

            if ($image) {
                $this->attachFeatured($article, $image, $user, $data['featured_image_alt'] ?? null);
            }

            $this->forgetFeeds();

            return $article->refresh();
        });
    }

    public function update(Article $article, User $user, array $data, ?UploadedFile $image = null): Article
    {
        return DB::transaction(function () use ($article, $user, $data, $image) {
            $article->fill($this->attributes($user, $data, $article));
            $article->save();
            $this->syncTags($article, $data['tags'] ?? null);

            if ($image) {
                $this->attachFeatured($article, $image, $user, $data['featured_image_alt'] ?? $article->featured_image_alt);
            } elseif (! empty($data['remove_featured_image'])) {
                $this->clearFeatured($article);
            } elseif (array_key_exists('featured_image_alt', $data)) {
                $article->update(['featured_image_alt' => $data['featured_image_alt']]);
            }

            $this->forgetFeeds();

            return $article->refresh();
        });
    }

    public function delete(Article $article): void
    {
        $article->delete();
        $this->forgetFeeds();
    }

    public function submit(Article $article): void
    {
        if (! in_array($article->status, [ArticleStatus::Draft, ArticleStatus::Rejected], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only drafts and rejected articles can be submitted for review.',
            ]);
        }

        $this->requireReady($article);

        $article->update([
            'status' => ArticleStatus::PendingReview,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function approve(Article $article, User $editor): void
    {
        if ($article->status !== ArticleStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => 'Only articles pending review can be approved.',
            ]);
        }

        $article->update([
            'status' => ArticleStatus::Approved,
            'reviewed_by' => $editor->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(Article $article, User $editor, string $reason): void
    {
        if (! in_array($article->status, [ArticleStatus::PendingReview, ArticleStatus::Approved], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only articles in review or approved can be rejected.',
            ]);
        }

        $article->update([
            'status' => ArticleStatus::Rejected,
            'rejection_reason' => $reason,
            'reviewed_by' => $editor->id,
            'reviewed_at' => now(),
        ]);
    }

    public function publish(Article $article, User $editor): void
    {
        if ($article->status !== ArticleStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => 'Approve the article before publishing it.',
            ]);
        }

        $this->requireReady($article);

        $article->update([
            'status' => ArticleStatus::Published,
            'published_at' => $article->published_at && $article->published_at->lte(now()) ? $article->published_at : now(),
            'scheduled_at' => null,
            'reviewed_by' => $editor->id,
            'reviewed_at' => $article->reviewed_at ?? now(),
        ]);

        $this->forgetFeeds();
    }

    public function schedule(Article $article, User $editor, \DateTimeInterface $when): void
    {
        if ($article->status !== ArticleStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => 'Approve the article before scheduling it.',
            ]);
        }

        if (now()->greaterThanOrEqualTo($when)) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Choose a future date and time.',
            ]);
        }

        $this->requireReady($article);

        $article->update([
            'status' => ArticleStatus::Scheduled,
            'scheduled_at' => $when,
            'published_at' => null,
            'reviewed_by' => $editor->id,
            'reviewed_at' => $article->reviewed_at ?? now(),
        ]);
    }

    public function archive(Article $article): void
    {
        if ($article->status !== ArticleStatus::Published) {
            throw ValidationException::withMessages([
                'status' => 'Only published articles can be archived.',
            ]);
        }

        $article->update(['status' => ArticleStatus::Archived]);
        $this->forgetFeeds();
    }

    public function publishDue(): int
    {
        $due = Article::query()
            ->where('status', ArticleStatus::Scheduled)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($due as $article) {
            $article->update([
                'status' => ArticleStatus::Published,
                'published_at' => $article->scheduled_at,
            ]);
        }

        if ($due->isNotEmpty()) {
            $this->forgetFeeds();
        }

        return $due->count();
    }

    public function recordView(Article $article): void
    {
        $seen = session('viewed_articles', []);
        if (in_array($article->id, $seen, true)) {
            return;
        }

        $article->increment('views_count');
        $article->views()->create([
            'visitor_hash' => hash('sha256', (string) session()->getId()),
        ]);

        $seen[] = $article->id;
        session(['viewed_articles' => array_slice($seen, -40)]);
    }

    private function attributes(User $user, array $data, ?Article $article = null): array
    {
        $authorId = $data['author_id'] ?? null;
        if (! $user->hasPermission('articles.review')) {
            $authorId = $user->authorOrCreate()->id;
        } elseif (blank($authorId)) {
            $authorId = $article?->author_id ?: $user->authorOrCreate()->id;
        }

        $slugSource = filled($data['slug'] ?? null) ? $data['slug'] : ($article->title ?? $data['title']);
        if ($article && blank($data['slug'] ?? null)) {
            $slug = $article->slug;
        } else {
            $slug = Article::uniqueSlug($slugSource, $article?->id);
        }

        return [
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'] ?? null,
            'author_id' => $authorId,
            'category_id' => $data['category_id'] ?? null,
            'is_breaking' => (bool) ($data['is_breaking'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_trending' => (bool) ($data['is_trending'] ?? false),
            'is_opinion' => (bool) ($data['is_opinion'] ?? false),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'og_image' => $data['og_image'] ?? null,
            'featured_image_alt' => $data['featured_image_alt'] ?? $article?->featured_image_alt,
            'status' => $article?->status ?? ArticleStatus::Draft,
        ];
    }

    private function requireReady(Article $article): void
    {
        $errors = [];
        if (blank($article->body)) {
            $errors['body'] = 'Add the article body before this step.';
        }
        if (! $article->category_id) {
            $errors['category_id'] = 'Choose a category before this step.';
        }
        if (! $article->author_id) {
            $errors['author_id'] = 'Choose an author before this step.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function syncTags(Article $article, ?string $tags): void
    {
        $names = collect(explode(',', (string) $tags))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique(fn (string $tag) => mb_strtolower($tag));

        $ids = $names->map(function (string $name) {
            $slug = Str::slug($name) ?: 'tag';
            $tag = Tag::query()->where('slug', $slug)->first();

            if (! $tag) {
                $tag = Tag::query()->create([
                    'name' => $name,
                    'slug' => Tag::uniqueSlug($name),
                ]);
            }

            return $tag->id;
        });

        $article->tags()->sync($ids->all());
    }

    private function attachFeatured(Article $article, UploadedFile $image, User $user, ?string $alt): void
    {
        $previousPath = $article->featured_image;
        $previousMedia = $article->featured_media_id;
        $media = $this->media->store($image, $user, ['alt_text' => $alt]);
        $article->update([
            'featured_media_id' => $media->id,
            'featured_image' => $media->path,
            'featured_image_alt' => $alt ?: $media->alt_text,
        ]);
        $this->deleteStoredImage($previousPath, $previousMedia, $media->path, $media->id);
    }

    private function clearFeatured(Article $article): void
    {
        $previousPath = $article->featured_image;
        $previousMedia = $article->featured_media_id;
        $article->update([
            'featured_image' => null,
            'featured_media_id' => null,
        ]);
        $this->deleteStoredImage($previousPath, $previousMedia);
    }

    private function deleteStoredImage(?string $path, ?int $mediaId, ?string $keepPath = null, ?int $keepMediaId = null): void
    {
        if (filled($path) && $path !== $keepPath && ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
            Storage::disk('public')->delete($path);
        }

        if ($mediaId && $mediaId !== $keepMediaId) {
            Media::query()->whereKey($mediaId)->delete();
        }
    }

    private function forgetFeeds(): void
    {
        Cache::forget('feed.sitemap');
        Cache::forget('feed.news');
        Cache::forget('feed.rss');
    }
}
