<?php

namespace App\Console\Commands;

use App\Models\ThreadsPost;
use App\Services\ThreadsApiService;
use Illuminate\Console\Command;
use Throwable;

class PublishDueThreadsPosts extends Command
{
    protected $signature = 'threads:publish-due';

    protected $description = '予約時刻を過ぎた Threads 投稿を自動で投稿する';

    public function handle(ThreadsApiService $threads): int
    {
        $duePosts = ThreadsPost::where('status', ThreadsPost::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        if ($duePosts->isEmpty()) {
            $this->info('投稿予定の下書きはありません。');
            return 0;
        }

        if (!$threads->isConfigured()) {
            $this->warn('Threads API の認証情報が未設定のため、投稿をスキップしました。');
            return 0;
        }

        foreach ($duePosts as $post) {
            try {
                $result = $threads->publishPost($post->body);
                $post->update([
                    'status' => ThreadsPost::STATUS_POSTED,
                    'posted_at' => now(),
                    'thread_id' => $result['id'],
                    'permalink' => $result['permalink'],
                    'error' => null,
                ]);
                $this->info('投稿しました: #' . $post->id);
            } catch (Throwable $e) {
                $post->update([
                    'status' => ThreadsPost::STATUS_FAILED,
                    'error' => $e->getMessage(),
                ]);
                $this->error('投稿に失敗しました #' . $post->id . ': ' . $e->getMessage());
            }
        }

        return 0;
    }
}
