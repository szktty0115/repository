<?php

namespace App\Services;

use RuntimeException;

/**
 * Anthropic (Claude) API を使って X 投稿の下書きを生成する。
 */
class PostGeneratorService
{
    private $apiKey;
    private $model;
    private $genre;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->model = config('services.anthropic.model') ?: 'claude-opus-4-7';
        $this->genre = config('services.x.genre') ?: 'プログラミング・技術';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * 投稿案のテキスト配列を返す。
     *
     * @return string[]
     */
    public function generateDrafts(int $count = 5): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Anthropic API キー（ANTHROPIC_API_KEY）が設定されていません。');
        }

        $count = max(1, min($count, 15));

        $system = 'あなたは日本語で発信する X（旧Twitter）アカウントの運用担当者です。'
            . 'アカウントのジャンルは「' . $this->genre . '」。'
            . 'フォロワーに価値を届け、自然に共感やフォローを得られる投稿を作るのが役割です。'
            . '各投稿のルール: 日本語で全角120文字以内、1投稿につき1メッセージ、'
            . 'URLや画像が前提の表現は使わない、ハッシュタグは多くても1つ、'
            . '誇張・釣り・スパム的な表現は禁止、読み手がすぐ実践できる具体性を持たせること。';

        $prompt = '次の条件で、X に投稿するツイート案を' . $count . '件作成してください。' . "\n"
            . '- ジャンル: ' . $this->genre . "\n"
            . '- 学習者や同業のエンジニアにとって役立つ、具体的な気づき・Tips・考え方を中心に' . "\n"
            . '- 出力は JSON 配列のみ。各要素はツイート本文の文字列とすること。' . "\n"
            . '- 説明文・見出し・コードブロックは付けない。' . "\n"
            . '例: ["ツイート1の本文", "ツイート2の本文"]';

        $payload = [
            'model' => $this->model,
            'max_tokens' => 3000,
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

        return $this->parseDrafts($text, $count);
    }

    /**
     * @return string[]
     */
    private function parseDrafts(string $text, int $count): array
    {
        $text = trim($text);
        $text = trim(preg_replace('/^```[a-zA-Z]*\s*|\s*```$/', '', $text));

        $drafts = [];
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $drafts[] = trim($item);
                }
            }
        }

        if (empty($drafts)) {
            foreach (preg_split('/\r?\n/', $text) as $line) {
                $line = trim(preg_replace('/^\s*(?:\d+[\.\)]|[-*])\s*/u', '', $line));
                if ($line !== '') {
                    $drafts[] = $line;
                }
            }
        }

        return array_slice($drafts, 0, $count);
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

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
