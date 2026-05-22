<?php

namespace App\Services;

use RuntimeException;
use Throwable;

/**
 * Threads (Meta) API への薄いラッパー。
 * 投稿は「コンテナ作成 → 公開」の2段階。認証は Threads の長期アクセストークン。
 */
class ThreadsApiService
{
    private const BASE = 'https://graph.threads.net/v1.0';

    private $accessToken;
    private $userId;
    private $username;

    public function __construct()
    {
        $config = config('services.threads', []);
        $this->accessToken = $config['access_token'] ?? null;
        $this->userId = $config['user_id'] ?? null;
        $this->username = $config['username'] ?? null;
    }

    public function isConfigured(): bool
    {
        return !empty($this->accessToken) && !empty($this->userId);
    }

    /**
     * テキスト投稿を公開し、投稿 id と permalink を返す。
     *
     * @return array{id:string,permalink:?string}
     */
    public function publishPost(string $text): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Threads API の認証情報（アクセストークン / ユーザーID）が設定されていません。');
        }

        $container = $this->send('POST', self::BASE . '/' . $this->userId . '/threads', [
            'media_type' => 'TEXT',
            'text' => $text,
            'access_token' => $this->accessToken,
        ]);
        if (!isset($container['id'])) {
            throw new RuntimeException('Threads 投稿コンテナの作成に失敗しました。');
        }

        $published = $this->send('POST', self::BASE . '/' . $this->userId . '/threads_publish', [
            'creation_id' => $container['id'],
            'access_token' => $this->accessToken,
        ]);
        if (!isset($published['id'])) {
            throw new RuntimeException('Threads への投稿公開に失敗しました。');
        }

        $permalink = null;
        try {
            $media = $this->send('GET', self::BASE . '/' . $published['id'], [
                'fields' => 'permalink',
                'access_token' => $this->accessToken,
            ]);
            $permalink = $media['permalink'] ?? null;
        } catch (Throwable $e) {
            // permalink が取れなくても投稿自体は成功しているため無視する。
        }

        return [
            'id' => (string) $published['id'],
            'permalink' => $permalink,
        ];
    }

    /**
     * フォロワー数を取得する。
     */
    public function getFollowerCount(): int
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Threads API の認証情報が設定されていません。');
        }

        $response = $this->send('GET', self::BASE . '/' . $this->userId . '/threads_insights', [
            'metric' => 'followers_count',
            'access_token' => $this->accessToken,
        ]);

        foreach ($response['data'] ?? [] as $metric) {
            if (($metric['name'] ?? '') === 'followers_count') {
                return (int) ($metric['total_value']['value'] ?? 0);
            }
        }

        throw new RuntimeException('フォロワー数の取得に失敗しました: ' . json_encode($response));
    }

    /**
     * 長期アクセストークンを更新する（有効期限は約60日）。
     *
     * @return array{access_token:string,expires_in:int}
     */
    public function refreshAccessToken(): array
    {
        if (empty($this->accessToken)) {
            throw new RuntimeException('更新対象の Threads アクセストークンが設定されていません。');
        }

        $response = $this->send('GET', 'https://graph.threads.net/refresh_access_token', [
            'grant_type' => 'th_refresh_token',
            'access_token' => $this->accessToken,
        ]);

        if (!isset($response['access_token'])) {
            throw new RuntimeException('アクセストークンの更新に失敗しました。');
        }

        return [
            'access_token' => (string) $response['access_token'],
            'expires_in' => (int) ($response['expires_in'] ?? 0),
        ];
    }

    private function send(string $method, string $url, array $params): array
    {
        $url .= '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Threads API への通信に失敗しました: ' . $error);
        }

        $data = json_decode($response, true);
        if ($status < 200 || $status >= 300) {
            $message = $data['error']['message'] ?? $response;
            throw new RuntimeException('Threads API がエラーを返しました (HTTP ' . $status . '): ' . $message);
        }

        return is_array($data) ? $data : [];
    }
}
