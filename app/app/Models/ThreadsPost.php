<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreadsPost extends Model
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
        'thread_id',
        'permalink',
        'error',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'posted_at' => 'datetime',
    ];
}
