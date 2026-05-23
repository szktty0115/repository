<?php

namespace App\Console\Commands;

use App\Models\ThreadsPost;
use App\Services\PostGeneratorService;
use Illuminate\Console\Command;
use Throwable;

class GenerateThreadsPosts extends Command
{
    protected $signature = 'threads:generate-posts {count=5 : 生成する投稿案の件数}';

    protected $description = 'Claude API を使って Threads 投稿の下書きを自動生成する';

    public function handle(PostGeneratorService $generator): int
    {
        if (!$generator->isConfigured()) {
            $this->warn('ANTHROPIC_API_KEY が未設定のため、投稿案の生成をスキップしました。');
            return 0;
        }

        $count = (int) $this->argument('count');

        try {
            $drafts = $generator->generateDrafts(
                $count,
                'Threads',
                config('services.threads.genre'),
                500
            );
        } catch (Throwable $e) {
            $this->error('投稿案の生成に失敗しました: ' . $e->getMessage());
            return 1;
        }

        foreach ($drafts as $body) {
            ThreadsPost::create([
                'body' => $body,
                'status' => ThreadsPost::STATUS_DRAFT,
                'source' => 'ai',
            ]);
        }

        $this->info(count($drafts) . ' 件の投稿案を作成しました。');
        return 0;
    }
}
