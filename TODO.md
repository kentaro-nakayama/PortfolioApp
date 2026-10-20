# TODOリスト

ページ・機能ごとの実装チェックリスト。`<!-- test: "..." -->` が付いている項目は、対応するPestテストが通ると
`composer todo`(= `php artisan todo:sync`)で自動的に `[x]` になります。手動で判断する項目は `(手動)` と記載し、
自動チェックの対象外にしています。

## Home (`/`)

- [x] ホーム画面(`/`)に到達できる <!-- test: "the home page is reachable" -->
- [x] 直近更新順に3件のプロジェクトダイジェストを表示する <!-- test: "the home page shows the most recently pushed repositories, limited to three" -->
- [ ] 自己紹介文を実際の内容に差し替える (手動)

## Projects一覧 (`/projects`)

- [x] 同期済みリポジトリの一覧が表示される <!-- test: "the project index page lists synced repositories" -->
- [x] 言語で絞り込みできる <!-- test: "the project index page can be filtered by language" -->
- [x] topicで絞り込みできる <!-- test: "the project index page can be filtered by topic" -->
- [x] リポジトリが0件のとき案内文を表示する <!-- test: "the project index page shows a hint when there are no repositories yet" -->

## Project詳細 (`/projects/{repository}`)

- [x] READMEをMarkdownとしてレンダリングする <!-- test: "the project show page renders the readme as markdown" -->
- [x] topicsバッジを表示する <!-- test: "the project show page displays topic badges" -->
- [x] READMEが無い場合に代替メッセージを表示する <!-- test: "the project show page shows a fallback message when there is no readme" -->

## About (`/about`)

- [x] Aboutページ(`/about`)に到達できる <!-- test: "the about page is reachable" -->
- [ ] 自己紹介・スキル・経歴の具体的な内容を書く (手動)

## GitHub同期バッチ (`php artisan github:sync`)

- [x] forkを除く自分のリポジトリを取得しDBへ保存する <!-- test: "github:sync stores owned repositories and excludes forks" -->
- [x] 既存レコードを更新し重複させない <!-- test: "github:sync updates an existing repository instead of duplicating it" -->
- [ ] Laravel Schedulerで毎日自動実行されるよう登録する (手動)

---

スコープ外(問い合わせフォーム、ダークモード、OGP/SEO対応など)は`CLAUDE.md`の「今後の検討事項」を参照。
