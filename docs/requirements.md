# 詳細要件定義

`CLAUDE.md`の要件定義を踏まえた、DBテーブル構成・ページ構成・各ページの機能の詳細仕様。
実装を変更した場合は、このファイルも合わせて更新すること。

## DBテーブル構成

### `repositories`

GitHub APIから取得したリポジトリ情報をキャッシュするテーブル。`php artisan github:sync`によってのみ書き込まれる。

| カラム | 型 | 説明 |
|---|---|---|
| id | bigint, PK | |
| github_id | unsigned bigint, unique | GitHub上のリポジトリID。同期時の突合キー |
| name | string | リポジトリ名 |
| slug | string, unique | URL用スラッグ(`name`をStr::slugしたもの)。ルートモデルバインディングのキー |
| full_name | string | `owner/repo`形式のフルネーム |
| description | text, nullable | GitHubのリポジトリ説明文 |
| html_url | string | GitHub上のリポジトリURL |
| language | string, nullable | 主要言語 |
| topics | json, nullable | GitHub topicsの配列 |
| stargazers_count | unsigned int, default 0 | star数 |
| forks_count | unsigned int, default 0 | fork数 |
| default_branch | string, default 'main' | デフォルトブランチ名 |
| readme | longtext, nullable | READMEの生Markdown |
| screenshot_url | string, nullable | README内の先頭画像から抽出したスクリーンショットURL(`ReadmeScreenshotExtractor`)。相対パスはraw.githubusercontent.comの絶対URLへ解決される |
| pushed_at | timestamp, nullable | GitHub上の最終push日時 |
| synced_at | timestamp, nullable | 最後にバッチ同期した日時 |
| created_at / updated_at | timestamp | Laravel標準タイムスタンプ |

- forkリポジトリ・privateリポジトリ・internalリポジトリは`github:sync`実行時点でAPIレスポンスの`fork` / `private` / `visibility`フラグにより除外され、そもそも保存されない。
- GitHubのプロフィールREADMEリポジトリ(リポジトリ名が`GITHUB_USERNAME`と一致するもの。例: `kentaro-nakayama/kentaro-nakayama`)は自己紹介用でありプロジェクトではないため除外する。自己紹介はAboutページが担う。
- 同期時にAPIレスポンスへ含まれなくなったリポジトリ(public→privateへの変更、リポジトリ削除、fork化など)はDBから物理削除される。これにより非公開化したリポジトリが画面に残り続けることはない。
- 認証・管理画面を持たないため、他にユーザー入力由来のテーブルは存在しない。

### その他(Laravel標準スキーマ)

`users` / `password_reset_tokens` / `sessions` / `cache` / `cache_locks` / `jobs` / `job_batches` / `failed_jobs` はLaravelスケルトン標準のテーブル。本アプリでは認証機能を持たないため`users`関連は未使用。`jobs`系はスケジューラ実行(`github:sync`)の将来的なキュー化に備えて残している。

## ページ構成

| # | パス | ルート名 | コントローラー / アクション | View |
|---|---|---|---|---|
| 1 | `/` | `home` | `HomeController@index` | `resources/views/home.blade.php` |
| 2 | `/projects` | `projects.index` | `ProjectController@index` | `resources/views/projects/index.blade.php` |
| 3 | `/projects/{repository:slug}` | `projects.show` | `ProjectController@show` | `resources/views/projects/show.blade.php` |
| 4 | `/about` | `about` | なし(`Route::view`) | `resources/views/about.blade.php` |

共通レイアウト: `resources/views/layouts/app.blade.php`(ヘッダーナビ: Home / Projects / About、フッター)。

## 各ページの機能

### 1. Home (`/`)

- **目的**: 簡単な自己紹介 + ポートフォリオのダイジェスト表示
- **データ取得**: `Repository`を`pushed_at`降順で3件取得(`HomeController@index`)
- **表示内容**:
  - 自己紹介セクション(名前・一言紹介・Projects/Aboutへの導線ボタン) ※現状プレースホルダーテキスト
  - "Recent Projects"セクション: 直近更新3件をカード表示(リポジトリ名・説明・言語・star数)、「すべて見る」で`/projects`へ
  - リポジトリが0件の場合は`php artisan github:sync`実行を促す案内文を表示

### 2. Projects一覧 (`/projects`)

- **目的**: 同期済みの全リポジトリ(forkを除く)を一覧表示し、言語・topicで絞り込めるようにする
- **データ取得**(`ProjectController@index`):
  - クエリパラメータ`language`・`topic`でフィルタ(`where('language', ...)` / `whereJsonContains('topics', ...)`)
  - `pushed_at`降順で一覧取得
  - フィルタ用の選択肢として、存在する`language`一覧と`topics`一覧(重複排除・ソート済み)を生成
- **表示内容**:
  - 言語・topicのセレクトボックス(変更時に自動送信、`onchange="this.form.submit()"`)
  - リポジトリカードグリッド(名前・説明・言語・star数・fork数)、クリックで詳細ページへ
  - 0件時は案内文を表示

### 3. Project詳細 (`/projects/{repository:slug}`)

- **目的**: 個別リポジトリの詳細情報とREADMEを表示
- **データ取得**(`ProjectController@show`): ルートモデルバインディング(`slug`)で`Repository`を1件取得
- **表示内容**:
  - 一覧へ戻るリンク
  - リポジトリ名・説明
  - 言語・star数・fork数・GitHubへの外部リンク
  - topicsバッジ一覧(存在する場合)
  - スクリーンショット画像(`screenshot_url`が存在する場合のみ)
  - README本文を`App\Services\GitHub\ReadmeRenderer::toHtml()`でHTML変換して表示(README無し時はその旨を表示)。
    README内の相対パス画像・リンクは`GithubRawUrlResolver`でGitHub上の絶対URLへ解決する

### 4. About (`/about`)

- **目的**: 自己紹介・スキル・経歴を掲載する静的ページ
- **データ取得**: なし(静的Blade view)
- **表示内容**: 自己紹介、これまでの経歴、エンジニアを目指したきっかけ、スキル(主に使用/学習経験あり/触れたことがある/資格)、今後の展望

## バッチ処理

- `php artisan github:sync`(`App\Console\Commands\SyncGithubRepositories`)
  - `GitHubClient::ownedRepositories()`で対象ユーザーの全public repoを取得(ページネーション対応)
    - 使用するエンドポイントは`GET /users/{username}/repos`。この仕様上privateリポジトリは返却されないが、
      多重防御として`fork` / `private` / `visibility`を明示的に検査し、public かつ 非forkのもののみを返す
    - リポジトリ名が`GITHUB_USERNAME`と一致するプロフィールREADMEリポジトリも除外する
  - 各リポジトリについて`GitHubClient::readme()`でREADMEを取得(除外されたリポジトリのREADMEは取得しない)
  - `Repository::updateOrCreate(['github_id' => ...], [...])`でDBへ反映
  - 今回取得した`github_id`に含まれないレコードを削除し、非公開化・削除済みのリポジトリをDBから除去する
  - `routes/console.php`で`Schedule::command('github:sync')->daily()`により毎日自動実行

## デプロイ (Laravel Cloud)

ホスティングはLaravel Cloud。GitHub連携による自動デプロイを利用し、`main`へのpushでデプロイされる。
プロジェクト作成・環境変数・DBアタッチはLaravel Cloudのダッシュボード(cloud.laravel.com)で操作する(CLIは提供されていない)。

### ランタイム

| 項目 | 値 | 根拠 |
|---|---|---|
| PHP | 8.4 | `composer.json`の`php: ^8.4` |
| Node | 24 | `.github/workflows/tests.yml`のCIと揃える |

### データベース

- Laravel CloudのマネージドMySQL 8を作成し、対象environmentにアタッチする
- `DB_CONNECTION` / `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD`はLaravel Cloudが自動注入するため、
  環境変数として手動設定してはいけない(上書きすると接続できなくなる)

### ビルド / デプロイコマンド

Build commands:

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
```

Deploy commands:

```bash
php artisan migrate --force
php artisan optimize
```

- `php artisan optimize`でconfig/route/viewのキャッシュをまとめて生成する
- `php artisan storage:link`は不要。アップロードファイルを持たず、スクリーンショットは`raw.githubusercontent.com`の
  外部URLを`repositories.screenshot_url`に保存して参照するだけのため、永続ストレージを使用しない

### 環境変数

```
APP_NAME=Portfolio
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<割り当てられたドメイン>
APP_LOCALE=ja
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

GITHUB_USERNAME=<GitHubユーザー名>
GITHUB_TOKEN=<Personal Access Token>
```

- `APP_KEY`はLaravel Cloudが環境作成時に自動生成する。既存の値を差し替えると既存セッションが復号できなくなるため触らない
- `APP_DEBUG=false`は必須。スタックトレースに`GITHUB_TOKEN`等が露出するのを防ぐ
- session / cache / queueはいずれもdatabaseドライバを使用し、必要なテーブル(`sessions`・`cache`・`jobs`)は
  Laravel標準のマイグレーションで作成される
- `GITHUB_TOKEN`はFine-grained tokenの「Public repositories (read-only)」で足りる。publicリポジトリの情報とREADMEしか
  読まないため追加スコープは不要。未設定でも動作するがGitHub APIのレート制限が60回/時に制限される

### スケジューラ

- environmentの設定でSchedulerを有効化する。これにより`routes/console.php`の
  `Schedule::command('github:sync')->daily()`が毎日実行される
- キューは使用していないためWorkerは不要

### 初回デプロイ後の手順

デプロイ直後はDBが空で何も表示されないため、ダッシュボードのコマンド実行から一度だけ手動で同期する。

```bash
php artisan github:sync
```

その後、`/`・`/projects`(言語/topic絞り込み含む)・`/projects/{repository}`・`/about`の表示を確認する。
