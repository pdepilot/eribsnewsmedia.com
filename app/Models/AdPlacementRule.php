<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdPlacementRule extends Model
{
    protected $fillable = [
        'ad_unit_id',
        'page_type',
        'device',
        'priority',
        'weight',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'weight' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(AdUnit::class, 'ad_unit_id');
    }
}
