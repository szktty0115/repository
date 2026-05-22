# Threads SNS 自動運用機能 セットアップガイド

この Laravel アプリには、Threads（Meta）アカウントの SNS 運用を半自動化する機能が含まれています。X 機能とは独立した `/threads` ダッシュボードで動作します。

## できること

- Claude（Anthropic API）でジャンルに沿った投稿文の下書きを自動生成
- 指定時刻での自動投稿（予約投稿）
- フォロワー数の推移グラフと「今月 +500」目標の進捗表示

## できないこと / 方針

- 「自動で確実にフォロワーが増える」ことは保証できません。本機能は運用の手間を減らすためのものです。
- 自動フォロー等の規約違反になる挙動は実装していません。

## 前提：アカウントについて

- **Threads は Instagram に紐づくサービスです。** Threads アカウントの作成・利用には Instagram アカウントが必要で、Facebook 単体では始められません。
- Threads API を使うには Meta 開発者アカウント（Facebook ログインで作成可）と開発者アプリが必要ですが、運用対象になるのは Threads（Instagram）アカウントです。

## 1. データベースの準備

```
php artisan migrate
```

`threads_posts` と `threads_follower_stats` テーブルが作成されます。

## 2. Threads API の準備

1. <https://developers.facebook.com/> でアプリを作成し、**「Threads API」のユースケース**を追加します。
2. アクセス許可（権限）として次を有効にします。
   - `threads_basic`
   - `threads_content_publish`（投稿に必要）
   - `threads_manage_insights`（フォロワー数取得に必要）
3. アプリ管理画面の Threads ユースケースから、自分の Threads アカウント用の
   **長期アクセストークン**を発行します（有効期限は約60日）。
4. ユーザー ID を取得します。トークン発行後、次を実行すると `id` が得られます。

   ```
   curl "https://graph.threads.net/v1.0/me?fields=id,username&access_token=取得したトークン"
   ```

5. `.env` に設定します。

```
THREADS_ACCESS_TOKEN=...
THREADS_USER_ID=...
THREADS_USERNAME=...                 # @ を除いたユーザー名（任意）
THREADS_CONTENT_GENRE=プログラミング・技術
```

## 3. Anthropic API キーの取得

X 機能と共通です。<https://console.anthropic.com/> で API キーを発行し、`.env` に設定します。

```
ANTHROPIC_API_KEY=...
ANTHROPIC_MODEL=claude-opus-4-7
```

設定変更後は `php artisan config:clear` を実行してください。

## 4. アクセストークンの更新（重要）

Threads の長期アクセストークンは**約60日で失効**します。失効前に次のコマンドを実行すると、
更新後の新しいトークンが表示されます。

```
php artisan threads:refresh-token
```

表示されたトークンを `.env` の `THREADS_ACCESS_TOKEN` に貼り替え、`php artisan config:clear`
を実行してください。

## 5. 自動実行（スケジューラ）

サーバーの cron に次の1行を登録します（X 機能と共通）。

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

登録されているスケジュール:

- `threads:generate-posts 7` — 毎週月曜 6:30 に投稿案を7件生成
- `threads:publish-due` — 15分ごとに予約投稿をチェックして投稿
- `threads:snapshot-followers` — 毎日 23:55 にフォロワー数を記録

## 6. 使い方

- ログイン後、`/threads` にアクセスするとダッシュボードが開きます。
- 画面から手動で生成・予約・投稿・フォロワー数入力ができます。
- コマンドからも実行できます。

```
php artisan threads:generate-posts 7
php artisan threads:publish-due
php artisan threads:snapshot-followers
```
