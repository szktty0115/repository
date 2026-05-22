<?php

namespace App\Console\Commands;

use App\Models\XPost;
use App\Services\XApiService;
use Illuminate\Console\Command;
use Throwable;

class PublishDueXPosts extends Command
{
    protected $signature = 'x:publish-due';

    protected $description = '予約時刻を過ぎた X 投稿を自動で投稿する';

    public function handle(XApiService $x): int
    {
        $duePosts = XPost::where('status', XPost::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        if ($duePosts->isEmpty()) {
            $this->info('投稿予定の下書きはありません。');
            return 0;
        }

        if (!$x->canPost()) {
            $this->warn('X API の認証情報が未設定のため、投稿をスキップしました。');
            return 0;
        }

        foreach ($duePosts as $post) {
            try {
                $tweetId = $x->postTweet($post->body);
                $post->update([
                    'status' => XPost::STATUS_POSTED,
                    'posted_at' => now(),
                    'tweet_id' => $tweetId,
                    'error' => null,
                ]);
                $this->info('投稿しました: #' . $post->id);
            } catch (Throwable $e) {
                $post->update([
                    'status' => XPost::STATUS_FAILED,
                    'error' => $e->getMessage(),
                ]);
                $this->error('投稿に失敗しました #' . $post->id . ': ' . $e->getMessage());
            }
        }

        return 0;
    }
}
