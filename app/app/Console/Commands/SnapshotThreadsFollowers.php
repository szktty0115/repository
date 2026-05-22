<?php

namespace App\Console\Commands;

use App\Models\ThreadsFollowerStat;
use App\Services\ThreadsApiService;
use Illuminate\Console\Command;
use Throwable;

class SnapshotThreadsFollowers extends Command
{
    protected $signature = 'threads:snapshot-followers';

    protected $description = 'Threads のフォロワー数を取得して日次で記録する';

    public function handle(ThreadsApiService $threads): int
    {
        if (!$threads->isConfigured()) {
            $this->warn('Threads API の認証情報が未設定のため、スキップしました。');
            return 0;
        }

        try {
            $followers = $threads->getFollowerCount();
        } catch (Throwable $e) {
            $this->error('フォロワー数の取得に失敗しました: ' . $e->getMessage());
            return 1;
        }

        ThreadsFollowerStat::updateOrCreate(
            ['recorded_on' => now()->toDateString()],
            [
                'followers_count' => $followers,
                'source' => 'api',
            ]
        );

        $this->info('フォロワー数を記録しました: ' . $followers);
        return 0;
    }
}
