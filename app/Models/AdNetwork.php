<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdNetwork extends Model
{
    public const TYPES = ['adsense', 'third_party', 'direct', 'ad_manager', 'monetag', 'adsterra', 'medianet', 'custom'];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'website_url',
        'publisher_id',
        'notes',
        'enabled',
        'priority',
        'configuration',
        'credentials',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'priority' => 'integer',
            'configuration' => 'array',
            'credentials' => 'encrypted',
        ];
    }

    public function units(): HasMany
    {
        return $this->hasMany(AdUnit::class);
    }

    public function hasCredentials(): bool
    {
        return filled($this->credentials);
    }
}
