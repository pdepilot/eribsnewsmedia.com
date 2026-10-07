<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdNetwork extends Model
{
    public const TYPES = ['adsense', 'third_party', 'direct', 'ad_manager'];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'publisher_id',
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
