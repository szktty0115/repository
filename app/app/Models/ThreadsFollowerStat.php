<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreadsFollowerStat extends Model
{
    protected $fillable = [
        'recorded_on',
        'followers_count',
        'source',
    ];

    protected $casts = [
        'recorded_on' => 'date',
    ];
}
