<?php

namespace App\Services;

use RuntimeException;

/**
 * Claude API を使って note(note.com) 用の長文記事の下書きを生成する。
 */
class NoteArticleGeneratorService
{
    private $apiKey;
    private $model;
    private $genre;
    private $targetChars;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->model = config('services.anthropic.model') ?: 'claude-opus-4-7';
        $this->genre = config('services.note.genre') ?: 'プログラミング・技術';
        $this->targetChars = (int) (config('services.note.target_chars') ?: 2500);
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * 1記事分の下書きを生成する。
     *
     * @return array{title:string,body:string,tags:string}
     */
    public function generateArticle(?string $topic = null): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Anthropic API キー（ANTHROPIC_API_KEY）が設定されていません。');
        }

        $target = $this->targetChars;

        $system = 'あなたは note (note.com) で発信する日本語のテック系ライターです。'
            . 'ジャンルは「' . $this->genre . '」。'
            . '読者が読み終わったあとに「読んでよかった」と感じる、'
            . '自分の経験や具体例に基づいた記事を書きます。'
            . "\n\n執筆ルール:\n"
            . "- 日本語、丁寧体（です・ます調）\n"
            . "- 本文は約" . $target . "文字\n"
            . "- 段落は短めに、2〜4文ごとに改行。段落間は空行を1行入れる\n"
            . "- 抽象論で終わらず、必ず具体例・コード例・数字・体験談などの具体性を含める\n"
            . "- 釣りタイトル、煽り、誇張は避ける\n"
            . "- 冒頭で読者の関心を引き、最後に持ち帰れる結論を1つ示す\n"
            . "- Markdown記法（#, **, など）は使わず、自然な日本語の段落構成で書く";

        $topicLine = $topic
            ? 'テーマ: ' . $topic
            : 'テーマ: 自由（' . $this->genre . ' に関する、読者の役に立つ具体的な話題から選定）';

        $prompt = "次の指定で note 記事を1本書いてください。\n\n"
            . $topicLine . "\n\n"
            . "出力は JSON のみ。説明文・コードブロックは付けない。次の形式で返してください。\n\n"
            . "{\n"
            . '  "title": "記事タイトル（30文字以内、内容を端的に表す）",' . "\n"
            . '  "body": "記事本文（約' . $target . '文字、改行と空行で段落分けされた自然な日本語）",' . "\n"
            . '  "tags": ["タグ1", "タグ2", "タグ3"]' . "\n"
            . "}";

        $payload = [
            'model' => $this->model,
            'max_tokens' => 10000,
            'system' => [
                [
                    'type' => 'text',
                    'text' => $system,
                    'cache_control' => ['type' => 'ephemeral'],
                ],
            ],
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $response = $this->requestMessages($payload);

        $text = '';
        foreach ($response['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'];
            }
        }

        return $this->parseArticle($text);
    }

    /**
     * @return array{title:string,body:string,tags:string}
     */
    private function parseArticle(string $text): array
    {
        $text = trim($text);
        $text = trim(preg_replace('/^```[a-zA-Z]*\s*|\s*```$/', '', $text));

        $decoded = json_decode($text, true);
        if (!is_array($decoded) || empty($decoded['body'])) {
            throw new RuntimeException('記事生成結果の JSON 解析に失敗しました。');
        }

        $tagsRaw = $decoded['tags'] ?? null;
        if (is_array($tagsRaw)) {
            $tags = implode(', ', array_filter(array_map('trim', $tagsRaw)));
        } else {
            $tags = is_string($tagsRaw) ? trim($tagsRaw) : '';
        }

        return [
            'title' => trim((string) ($decoded['title'] ?? '無題')),
            'body' => trim((string) $decoded['body']),
            'tags' => $tags,
        ];
    }

    private function requestMessages(array $payload): array
    {
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Anthropic API への通信に失敗しました: ' . $error);
        }

        $data = json_decode($response, true);
        if ($status < 200 || $status >= 300) {
            $message = $data['error']['message'] ?? $response;
            throw new RuntimeException('Anthropic API がエラーを返しました (HTTP ' . $status . '): ' . $message);
        }

        return is_array($data) ? $data : [];
    }
}
