<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPlacement extends Model
{
    public const DEVICES = ['all', 'desktop', 'tablet', 'mobile'];

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'device',
        'enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function units(): HasMany
    {
        return $this->hasMany(AdUnit::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(AdCampaign::class);
    }
}
