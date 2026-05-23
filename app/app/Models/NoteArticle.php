<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteArticle extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'body',
        'tags',
        'status',
        'source',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
