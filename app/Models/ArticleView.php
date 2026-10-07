<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleView extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'article_id',
        'visitor_hash',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
