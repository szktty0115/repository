<?php

namespace App\Console\Commands;

use App\Services\ThreadsApiService;
use Illuminate\Console\Command;
use Throwable;

class RefreshThreadsToken extends Command
{
    protected $signature = 'threads:refresh-token';

    protected $description = 'Threads の長期アクセストークンを更新し、新しいトークンを表示する';

    public function handle(ThreadsApiService $threads): int
    {
        if (!$threads->isConfigured()) {
            $this->warn('Threads API の認証情報が未設定です。');
            return 0;
        }

        try {
            $result = $threads->refreshAccessToken();
        } catch (Throwable $e) {
            $this->error('アクセストークンの更新に失敗しました: ' . $e->getMessage());
            return 1;
        }

        $days = (int) round($result['expires_in'] / 86400);

        $this->info('新しい Threads アクセストークン:');
        $this->line($result['access_token']);
        $this->info('有効期限: 約 ' . $days . ' 日');
        $this->warn('.env の THREADS_ACCESS_TOKEN をこの値に更新し、php artisan config:clear を実行してください。');

        return 0;
    }
}
