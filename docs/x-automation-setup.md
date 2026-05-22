# X SNS 自動運用機能 セットアップガイド

この Laravel アプリには、X（旧Twitter）アカウントの SNS 運用を半自動化する機能が含まれています。

## できること

- Claude（Anthropic API）でジャンルに沿った投稿文の下書きを自動生成
- 指定時刻での自動投稿（予約投稿）
- フォロワー数の推移グラフと「今月 +500」目標の進捗表示

## できないこと / 方針

- 「自動で確実にフォロワーが増える」ことは保証できません。フォロワーの増加は投稿内容の質に依存します。本機能は運用の手間を減らすためのものです。
- 自動フォロー / フォロー解除 / 大量いいね等の bot 行為は X の利用規約違反（凍結リスク）のため、実装していません。

## 1. データベースの準備

```
php artisan migrate
```

`x_posts` と `x_follower_stats` テーブルが作成されます。

## 2. X API キーの取得

1. <https://developer.x.com/> で開発者アカウントを作成し、アプリを登録します。
2. プランを選択します。
   - **Free**: 投稿は可能（月1,500件程度）。フォロワー数などの読み取りはほぼ不可。
   - **Basic**（月200ドル前後）: 読み取りも可能。
3. アプリの権限（User authentication settings）を **Read and write** に設定します。
4. 次のキーを発行します: API Key, API Secret, Access Token, Access Token Secret。
5. `.env` に設定します（`.env.example` 参照）。

```
X_API_KEY=...
X_API_SECRET=...
X_ACCESS_TOKEN=...
X_ACCESS_TOKEN_SECRET=...
X_BEARER_TOKEN=...        # フォロワー数の読み取りに使用（任意）
X_USERNAME=sknacdma      # @ を除いたユーザー名
X_CONTENT_GENRE=プログラミング・技術
```

## 3. Anthropic API キーの取得

1. <https://console.anthropic.com/> で API キーを発行します。
2. `.env` に設定します。

```
ANTHROPIC_API_KEY=...
ANTHROPIC_MODEL=claude-opus-4-7
```

設定変更後は `php artisan config:clear` を実行してください。

## 4. 自動実行（スケジューラ）

サーバーの cron に次の1行を登録すると、生成・投稿・記録が自動で回ります。

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

登録されているスケジュール:

- `x:generate-posts 7` — 毎週月曜 6:00 に投稿案を7件生成
- `x:publish-due` — 15分ごとに予約投稿をチェックして投稿
- `x:snapshot-followers` — 毎日 23:50 にフォロワー数を記録

## 5. 使い方

- ログイン後、`/x` にアクセスするとダッシュボードが開きます。
- 画面から手動で生成・予約・投稿・フォロワー数入力ができます。
- コマンドからも実行できます。

```
php artisan x:generate-posts 7
php artisan x:publish-due
php artisan x:snapshot-followers
```

## フォロワー数について

Free プランではフォロワー数の API 取得が制限されます。その場合はダッシュボードの
「フォロワー数を記録」フォームから手動で入力すれば、推移グラフと「今月 +500」の
目標進捗は問題なく利用できます。
