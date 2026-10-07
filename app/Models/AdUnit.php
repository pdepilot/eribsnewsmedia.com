<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdUnit extends Model
{
    public const FORMATS = ['auto', 'rectangle', 'horizontal', 'vertical', 'fluid'];

    protected $fillable = [
        'ad_network_id',
        'ad_placement_id',
        'name',
        'ad_client',
        'ad_slot',
        'format',
        'responsive',
        'device',
        'enabled',
        'priority',
        'markup',
    ];

    protected function casts(): array
    {
        return [
            'responsive' => 'boolean',
            'enabled' => 'boolean',
            'priority' => 'integer',
        ];
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
