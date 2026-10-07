<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsTxtLine extends Model
{
    protected $fillable = [
        'line',
        'sort_order',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
