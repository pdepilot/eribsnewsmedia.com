<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Advertisement extends Model
{
    public const PLACEMENTS = [
        'header',
        'homepage_top',
        'homepage_middle',
        'sidebar',
        'article_top',
        'article_middle',
        'article_bottom',
        'footer',
        'mobile',
    ];

    protected $fillable = [
        'name',
        'placement',
        'type',
        'code',
        'image_path',
        'target_url',
        'alt_text',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function imageUrl(): ?string
    {
        if (blank($this->image_path)) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        $disk = Storage::disk('public');

        if (! $disk instanceof FilesystemAdapter) {
            return null;
        }

        return $disk->url($this->image_path);
    }
}
