<?php

namespace App\Services;

use RuntimeException;

/**
 * X (Twitter) API v2 への薄いラッパー。
 * 投稿は OAuth 1.0a User Context、フォロワー数取得は Bearer もしくは OAuth 1.0a を利用する。
 */
class XApiService
{
    private $apiKey;
    private $apiSecret;
    private $accessToken;
    private $accessTokenSecret;
    private $bearerToken;
    private $username;

    public function __construct()
    {
        $config = config('services.x', []);
        $this->apiKey = $config['api_key'] ?? null;
        $this->apiSecret = $config['api_secret'] ?? null;
        $this->accessToken = $config['access_token'] ?? null;
        $this->accessTokenSecret = $config['access_token_secret'] ?? null;
        $this->bearerToken = $config['bearer_token'] ?? null;
        $this->username = $config['username'] ?? null;
    }

    /**
     * ツイート投稿に必要な認証情報がそろっているか。
     */
    public function canPost(): bool
    {
        return $this->apiKey && $this->apiSecret && $this->accessToken && $this->accessTokenSecret;
    }

    /**
     * フォロワー数取得に必要な認証情報がそろっているか。
     */
    public function canReadMetrics(): bool
    {
        return ($this->bearerToken && $this->username) || $this->canPost();
    }

    /**
     * ツイートを投稿し、投稿された tweet id を返す。
     */
    public function postTweet(string $text): string
    {
        if (!$this->canPost()) {
            throw new RuntimeException('X API の認証情報（APIキー / アクセストークン）が設定されていません。');
        }

        $url = 'https://api.twitter.com/2/tweets';
        $response = $this->send('POST', $url, [
            'Authorization: ' . $this->oauthHeader('POST', $url, []),
            'Content-Type: application/json',
        ], json_encode(['text' => $text]));

        $data = json_decode($response, true);
        if (!isset($data['data']['id'])) {
            throw new RuntimeException('ツイートの投稿に失敗しました: ' . $response);
        }

        return (string) $data['data']['id'];
    }

    /**
     * アカウントの公開指標（フォロワー数など）を取得する。
     *
     * @return array{followers_count:int,following_count:int,tweet_count:int}
     */
    public function getAccountMetrics(): array
    {
        if ($this->bearerToken && $this->username) {
            $url = 'https://api.twitter.com/2/users/by/username/' . rawurlencode($this->username)
                . '?user.fields=public_metrics';
            $response = $this->send('GET', $url, [
                'Authorization: Bearer ' . $this->bearerToken,
            ]);
        } elseif ($this->canPost()) {
            $base = 'https://api.twitter.com/2/users/me';
            $query = ['user.fields' => 'public_metrics'];
            $response = $this->send('GET', $base . '?' . http_build_query($query), [
                'Authorization: ' . $this->oauthHeader('GET', $base, $query),
            ]);
        } else {
            throw new RuntimeException('フォロワー数取得用の X API 認証情報が設定されていません。');
        }

        $data = json_decode($response, true);
        $metrics = $data['data']['public_metrics'] ?? null;
        if (!is_array($metrics)) {
            throw new RuntimeException('フォロワー数の取得に失敗しました: ' . $response);
        }

        return [
            'followers_count' => (int) ($metrics['followers_count'] ?? 0),
            'following_count' => (int) ($metrics['following_count'] ?? 0),
            'tweet_count' => (int) ($metrics['tweet_count'] ?? 0),
        ];
    }

    /**
     * OAuth 1.0a の Authorization ヘッダ値を生成する。
     * JSON ボディの POST ではボディは署名対象に含めないため、$queryParams のみ渡す。
     */
    private function oauthHeader(string $method, string $url, array $queryParams): string
    {
        $oauth = [
            'oauth_consumer_key' => $this->apiKey,
            'oauth_nonce' => bin2hex(random_bytes(16)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) time(),
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        ];

        $signatureParams = array_merge($oauth, $queryParams);
        ksort($signatureParams);
        $pairs = [];
        foreach ($signatureParams as $key => $value) {
            $pairs[] = rawurlencode($key) . '=' . rawurlencode($value);
        }

        $baseString = strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode(implode('&', $pairs));
        $signingKey = rawurlencode($this->apiSecret) . '&' . rawurlencode($this->accessTokenSecret);
        $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));

        ksort($oauth);
        $headerParts = [];
        foreach ($oauth as $key => $value) {
            $headerParts[] = rawurlencode($key) . '="' . rawurlencode($value) . '"';
        }

        return 'OAuth ' . implode(', ', $headerParts);
    }

    private function send(string $method, string $url, array $headers, string $body = null): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('X API への通信に失敗しました: ' . $error);
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('X API がエラーを返しました (HTTP ' . $status . '): ' . $response);
        }

        return $response;
    }
}
