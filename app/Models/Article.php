<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Article extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image',
        'featured_image_alt',
        'featured_media_id',
        'author_id',
        'category_id',
        'status',
        'published_at',
        'scheduled_at',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'is_breaking',
        'is_featured',
        'is_trending',
        'is_opinion',
        'views_count',
        'seo_title',
        'seo_description',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'is_breaking' => 'boolean',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'is_opinion' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }

    public function scopeMostViewed(Builder $query): Builder
    {
        return $query->orderByDesc('views_count')->orderByDesc('id');
    }

    public function scopeMostViewedSince(Builder $query, \DateTimeInterface $since): Builder
    {
        return $query->withCount([
            'views as period_views' => fn (Builder $views) => $views->where('created_at', '>=', $since),
        ])->orderByDesc('period_views')->orderByDesc('views_count');
    }

    public function scopeMostViewedToday(Builder $query): Builder
    {
        return $query->mostViewedSince(now()->timezone('Africa/Lagos')->startOfDay());
    }

    public function scopeMostViewedThisWeek(Builder $query): Builder
    {
        return $query->mostViewedSince(now()->timezone('Africa/Lagos')->startOfWeek());
    }

    public function scopeMostViewedThisMonth(Builder $query): Builder
    {
        return $query->mostViewedSince(now()->timezone('Africa/Lagos')->startOfMonth());
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ArticleStatus::Published)
            ->where(function (Builder $query) {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function isPublic(): bool
    {
        return $this->status === ArticleStatus::Published
            && ($this->published_at === null || $this->published_at->lte(now()));
    }

    public function postedLabel(): ?string
    {
        if ($this->published_at === null) {
            return null;
        }

        return $this->published_at->timezone('Africa/Lagos')->format('j M Y, g:i A');
    }

    public function summary(): string
    {
        if (filled($this->excerpt)) {
            return $this->excerpt;
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->body)) ?? '');

        return Str::limit($plain, 180);
    }

    public function featuredImageUrl(): ?string
    {
        if (blank($this->featured_image)) {
            return null;
        }

        if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
            return $this->featured_image;
        }

        $path = ltrim(str_replace('\\', '/', $this->featured_image), '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $disk = Storage::disk('public');

        if (! $disk instanceof FilesystemAdapter) {
            return null;
        }

        return $this->urlOnCurrentHost($disk->url($path));
    }

    private function urlOnCurrentHost(string $url): string
    {
        $host = request()->getSchemeAndHttpHost();
        $path = parse_url($url, PHP_URL_PATH);

        if ($host === '' || $path === null || $path === '') {
            return $url;
        }

        $absolute = rtrim($host, '/').'/'.ltrim($path, '/');
        $query = parse_url($url, PHP_URL_QUERY);

        return $query ? $absolute.'?'.$query : $absolute;
    }

    public function openGraphImage(): ?string
    {
        if (filled($this->og_image)) {
            if (str_starts_with($this->og_image, 'http://') || str_starts_with($this->og_image, 'https://')) {
                return $this->og_image;
            }

            return url($this->og_image);
        }

        return $this->featuredImageUrl();
    }

    public function canonicalUrl(): string
    {
        $path = '/'.ltrim(route('articles.show', $this, false), '/');
        $root = rtrim((string) config('app.url'), '/');

        if ($root === '') {
            $root = rtrim(url('/'), '/');
        }

        if ($this->canonicalUsesHttps($root)) {
            $root = preg_replace('#^http://#i', 'https://', $root) ?? $root;
        }

        $absolute = $root.$path;
        $query = strpos($absolute, '?');

        return $query === false ? $absolute : substr($absolute, 0, $query);
    }

    private function canonicalUsesHttps(string $root): bool
    {
        if (str_starts_with(strtolower($root), 'https://')) {
            return true;
        }

        if (app()->environment('production') || $this->requestIsSecure()) {
            return true;
        }

        $host = strtolower((string) parse_url($root, PHP_URL_HOST));

        return ! in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    private function requestIsSecure(): bool
    {
        return app()->bound('request') && request()->isSecure();
    }

    public function seoTitle(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function seoDescription(): string
    {
        return $this->seo_description ?: $this->summary();
    }

    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
