<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XFollowerStat extends Model
{
    protected $fillable = [
        'recorded_on',
        'followers_count',
        'following_count',
        'tweet_count',
        'source',
    ];

    protected $casts = [
        'recorded_on' => 'date',
    ];
}
