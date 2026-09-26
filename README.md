# Portfolio App

GitHubに公開しているプロジェクトを紹介するポートフォリオサイト。要件定義は [CLAUDE.md](./CLAUDE.md) を参照してください。

## セットアップ

```sh
composer install
npm install

cp .env.example .env
php artisan key:generate
```

`.env` に以下を設定してください。

- `DB_*`: ローカルのMySQL接続情報
- `GITHUB_USERNAME`: リポジトリを取得するGitHubアカウント名
- `GITHUB_TOKEN`: (任意) GitHub Personal Access Token。レート制限緩和のために設定推奨

```sh
php artisan migrate
php artisan github:sync   # GitHubからリポジトリ情報を取得してDBに保存
npm run build             # または npm run dev
php artisan serve
```

## テスト

```sh
./vendor/bin/pest
./vendor/bin/pint --test
```
