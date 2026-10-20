# CLAUDE.md

このファイルは、GitHubに公開しているプロジェクトを紹介するポートフォリオサイトの要件定義です。
実装に着手する際は、本ファイルの内容を前提とし、変更が必要な場合はユーザーに確認してください。

DBテーブル構成・ページ構成・各ページの詳細機能は [docs/requirements.md](docs/requirements.md) を参照。
実装を変更した場合は、そちらも合わせて更新すること。

## プロジェクト概要

- **目的**: GitHub上で公開している自分のプロジェクトを紹介するポートフォリオサイト
- **想定利用者**: 採用担当者、他のエンジニア、自分自身の実績整理
- **公開範囲**: 全ページ公開。管理画面・認証機能は持たない

## 技術スタック

| 項目 | 選定 |
|---|---|
| バックエンド | Laravel (最新安定版, PHP 8.3+) |
| フロントエンド | Blade + Tailwind CSS |
| データベース | MySQL |
| 外部API | GitHub REST API |
| テスト | Pest |
| CI | GitHub Actions（push/PR時にテスト自動実行） |
| ホスティング | Laravel Cloud |
| バッチ実行 | Laravel Scheduler（`php artisan schedule:run`） |

## 機能要件

### コア機能

1. **プロジェクト一覧ページ**
   - 自分の全public repository（forkは除外）をGitHub APIから取得し、DBにキャッシュして表示
   - リポジトリ名、説明、主要言語、star数、更新日時などを表示
2. **プロジェクト詳細ページ**
   - README内容の表示(Markdownレンダリング)
   - 言語構成、topics、star/fork数などの詳細情報
3. **About / プロフィールページ**
   - 自己紹介、スキル、経歴などを載せる静的ページ
4. **言語/タグによる絞り込み・検索**
   - 使用言語やGitHub topicsでのフィルタリング

### スコープ外(今回は実装しない)

- 問い合わせフォーム
- ダークモード
- OGP/SEO対応
- 認証・管理画面

これらは将来必要になった場合に追加検討する。

## データ管理・同期方式

- GitHub APIから取得する情報: リポジトリ一覧、説明、使用言語、star/fork数、topics、README、更新日時
- 同期用のArtisanコマンド(例: `php artisan github:sync`)を作成し、Laravel Schedulerで定期実行してDBを更新する
- 画面表示は常にDB(MySQL)から行い、リクエストの都度GitHub APIを叩かない(レート制限回避、表示速度確保)
- GitHub Personal Access Tokenを`.env`で管理し、API呼び出しの認証・レート制限緩和に使用する
- fork判定・取得対象リポジトリの絞り込みはGitHub API のレスポンス(`fork`フラグ等)を用いる

## インフラ・デプロイ

- ホスティング: Laravel Cloud
- データベース: MySQL(Laravel Cloud上のマネージドDBを利用)
- ローカル開発DB: 普段使いのMAMP付属MySQL(8.0, ポート3306)を使用。`portfolio`という専用データベースを作成して利用する
- 環境変数: `.env`(本番はLaravel Cloudの環境変数管理機能を利用)
- CI/CD: GitHub ActionsでPestテストを実行し、Laravel Cloudのデプロイ連携(Git連携による自動デプロイ)を利用する

## テスト方針

- Pestでユニットテスト・機能テストを作成する
- GitHub Actionsでpush/PR時に自動実行する
- GitHub API連携部分は実際のAPIを叩かず、HTTPクライアントをモックしてテストする
- 意味のないアサーション(例: `expect(true)->toBe(true)`)やテストを通すためだけのハードコードは禁止(グローバルCLAUDE.mdのルールに準拠)

## 開発ルール(グローバルCLAUDE.mdより継承・特記)

- 新規実装や大きな変更に着手する前に、技術スタックや実装方針をユーザーに提示し、合意を得てから作業を開始する
- 明示的な指示がない限り、`git commit` / `git push` を自動実行しない
- ファイルやデータベースを不可逆的に削除・破壊する操作を行う場合は、実行前に必ず確認する
- テストは実際の機能を検証し、境界値・異常系・エラーケースも含める

## 今後の検討事項

- 問い合わせフォームの要否とMailer設定
- ダークモード対応
- OGP/SEO対応(メタタグ、OGP画像生成など)
- 一覧ページのページネーション/無限スクロールなどUI詳細
- GitHub Webhookによるリアルタイム更新への切り替え(現状はスケジューラによる定期バッチのみ)
