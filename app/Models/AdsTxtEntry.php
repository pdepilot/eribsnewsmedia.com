<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsTxtEntry extends Model
{
    protected $fillable = [
        'advertising_system',
        'publisher_account_id',
        'relationship',
        'certification_authority_id',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function line(): string
    {
        $parts = [
            $this->advertising_system,
            $this->publisher_account_id,
            strtoupper((string) $this->relationship),
        ];

        if (filled($this->certification_authority_id)) {
            $parts[] = $this->certification_authority_id;
        }

        return implode(', ', $parts);
    }
}
