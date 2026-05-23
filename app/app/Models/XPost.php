<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XPost extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_POSTED = 'posted';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'body',
        'status',
        'source',
        'scheduled_at',
        'posted_at',
        'tweet_id',
        'error',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'posted_at' => 'datetime',
    ];
}
