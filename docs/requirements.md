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
  - README本文を`Illuminate\Support\Str::markdown()`でHTML変換して表示(README無し時はその旨を表示)

### 4. About (`/about`)

- **目的**: 自己紹介・スキル・経歴を掲載する静的ページ
- **データ取得**: なし(静的Blade view)
- **表示内容**: 現状プレースホルダーテキストのみ。今後、経歴・スキルセットなどの具体的な内容を追記する

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
