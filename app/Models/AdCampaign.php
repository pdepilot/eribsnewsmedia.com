<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdCampaign extends Model
{
    public const STATUSES = ['draft', 'scheduled', 'active', 'paused', 'ended'];

    protected $fillable = [
        'advertiser_id',
        'ad_placement_id',
        'creative_id',
        'name',
        'start_at',
        'end_at',
        'priority',
        'status',
        'click_url',
        'impressions',
        'clicks',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'priority' => 'integer',
            'impressions' => 'integer',
            'clicks' => 'integer',
        ];
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(AdPlacement::class, 'ad_placement_id');
    }

    public function creative(): BelongsTo
    {
        return $this->belongsTo(AdCreative::class, 'creative_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AdEvent::class);
    }
}
