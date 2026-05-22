<?php

namespace App\Console\Commands;

use App\Models\XFollowerStat;
use App\Services\XApiService;
use Illuminate\Console\Command;
use Throwable;

class SnapshotXFollowers extends Command
{
    protected $signature = 'x:snapshot-followers';

    protected $description = 'X のフォロワー数を取得して日次で記録する';

    public function handle(XApiService $x): int
    {
        if (!$x->canReadMetrics()) {
            $this->warn('フォロワー数取得用の X API 認証情報が未設定のため、スキップしました。');
            return 0;
        }

        try {
            $metrics = $x->getAccountMetrics();
        } catch (Throwable $e) {
            $this->error('フォロワー数の取得に失敗しました: ' . $e->getMessage());
            return 1;
        }

        XFollowerStat::updateOrCreate(
            ['recorded_on' => now()->toDateString()],
            [
                'followers_count' => $metrics['followers_count'],
                'following_count' => $metrics['following_count'],
                'tweet_count' => $metrics['tweet_count'],
                'source' => 'api',
            ]
        );

        $this->info('フォロワー数を記録しました: ' . $metrics['followers_count']);
        return 0;
    }
}
