<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdUnit extends Model
{
    public const FORMATS = ['auto', 'display', 'responsive', 'native', 'banner', 'html', 'script', 'image', 'custom', 'rectangle', 'horizontal', 'vertical', 'fluid'];

    protected $fillable = [
        'ad_network_id',
        'ad_placement_id',
        'name',
        'slug',
        'ad_client',
        'ad_slot',
        'format',
        'responsive',
        'device',
        'page_target',
        'enabled',
        'priority',
        'weight',
        'markup',
        'fallback_code',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'responsive' => 'boolean',
            'enabled' => 'boolean',
            'priority' => 'integer',
            'weight' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeCurrentlyRunning($query)
    {
        return $query->where(function ($inner) {
            $inner->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($inner) {
            $inner->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        });
    }

    public function scopeForPlacement($query, int $placementId)
    {
        return $query->where('ad_placement_id', $placementId);
    }

    public function scopeForDevice($query, string $device)
    {
        return $query->where(function ($inner) use ($device) {
            $inner->where('device', 'all')->orWhere('device', $device);
        });
    }

    public function scopeForPageType($query, string $page)
    {
        return $query->where(function ($inner) use ($page) {
            $inner->where('page_target', 'all')->orWhere('page_target', $page);
        });
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(AdNetwork::class, 'ad_network_id');
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(AdPlacement::class, 'ad_placement_id');
    }
}
