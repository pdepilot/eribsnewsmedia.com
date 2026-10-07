<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertiser extends Model
{
    public const STATUSES = ['active', 'paused', 'archived'];

    protected $fillable = [
        'name',
        'company',
        'contact_name',
        'email',
        'phone',
        'website',
        'address',
        'notes',
        'status',
    ];

    public function creatives(): HasMany
    {
        return $this->hasMany(AdCreative::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(AdCampaign::class);
    }
}
