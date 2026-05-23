<?php

namespace App\Console\Commands;

use App\Models\XPost;
use App\Services\PostGeneratorService;
use Illuminate\Console\Command;
use Throwable;

class GenerateXPosts extends Command
{
    protected $signature = 'x:generate-posts {count=5 : 生成する投稿案の件数}';

    protected $description = 'Claude API を使って X 投稿の下書きを自動生成する';

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
                'X（旧Twitter）',
                config('services.x.genre'),
                120
            );
        } catch (Throwable $e) {
            $this->error('投稿案の生成に失敗しました: ' . $e->getMessage());
            return 1;
        }

        foreach ($drafts as $body) {
            XPost::create([
                'body' => $body,
                'status' => XPost::STATUS_DRAFT,
                'source' => 'ai',
            ]);
        }

        $this->info(count($drafts) . ' 件の投稿案を作成しました。');
        return 0;
    }
}
