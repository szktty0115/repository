<?php

namespace App\Console\Commands;

use App\Models\NoteArticle;
use App\Services\NoteArticleGeneratorService;
use Illuminate\Console\Command;
use Throwable;

class GenerateNoteArticles extends Command
{
    protected $signature = 'note:generate-articles {count=1 : 生成する記事の本数} {--topic= : 指定するテーマ（任意）}';

    protected $description = 'Claude API を使って note 記事の下書きを生成する';

    public function handle(NoteArticleGeneratorService $generator): int
    {
        if (!$generator->isConfigured()) {
            $this->warn('ANTHROPIC_API_KEY が未設定のため、記事の生成をスキップしました。');
            return 0;
        }

        $count = max(1, min(3, (int) $this->argument('count')));
        $topic = $this->option('topic') ?: null;

        $created = 0;
        for ($i = 0; $i < $count; $i++) {
            try {
                $article = $generator->generateArticle($topic);
            } catch (Throwable $e) {
                $this->error('記事の生成に失敗しました: ' . $e->getMessage());
                return 1;
            }

            NoteArticle::create([
                'title' => $article['title'],
                'body' => $article['body'],
                'tags' => $article['tags'],
                'status' => NoteArticle::STATUS_DRAFT,
                'source' => 'ai',
            ]);
            $created++;
        }

        $this->info($created . ' 件の記事を作成しました。');
        return 0;
    }
}
