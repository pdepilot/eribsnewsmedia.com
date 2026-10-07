<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class AdCreative extends Model
{
    protected $table = 'advertisement_creatives';

    protected $fillable = [
        'advertiser_id',
        'name',
        'type',
        'image_path',
        'html',
        'alt_text',
        'width',
        'height',
    ];

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
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
