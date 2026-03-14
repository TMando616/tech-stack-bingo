# Tech Stack Bingo (技術スタック・ビンゴ)

自分の学習した技術スタックや習得したスキルをビンゴ形式で管理・可視化できるアプリケーションです。

## 主な機能
- **複数ボード管理**: 「フロントエンド」「インフラ」など、カテゴリごとにビンゴボードを作成可能。
- **5x5 ビンゴグリッド**: 習得した技術をチェックし、達成日を記録。
- **ビンゴ判定**: 縦・横・斜めのラインが揃うと自動でカウント。
- **項目編集**: 各マスのラベルを自由にカスタマイズ（専用モーダルUI）。
- **認証機能**: ユーザー登録・ログインによる個人データの保護。

## 技術スタック
- **Frontend**: Vue.js 3 (Composition API / TypeScript), Vite
- **Backend**: Laravel 12 (PHP 8.4)
- **Database**: MySQL 8.4
- **Infrastructure**: Docker Compose (Laravel Sail)

---

## 開発環境の構築手順

Docker環境がインストールされていることを前提としています。

### 1. リポジトリのクローン
```bash
git clone <repository-url>
cd tech-stack-bingo
```

### 2. 環境変数の設定
バックエンドの設定ファイルをコピーします。
```bash
cp backend/.env.example backend/.env
```

### 3. コンテナのビルドと起動
初回起動時はビルドに数分かかる場合があります。
```bash
docker compose up -d --build
```

### 4. 依存関係のインストール (PHP)
```bash
docker compose exec laravel.test composer install
```

### 5. アプリケーションキーの生成
```bash
docker compose exec laravel.test php artisan key:generate
```

### 6. データベースのマイグレーションと初期データの投入
```bash
docker compose exec laravel.test php artisan migrate --seed
```

---

## アクセス方法

- **Frontend**: [http://localhost:5173](http://localhost:5173)
  - ホットリロードが有効です。コードを変更すると即座に反映されます。
- **Backend (API)**: [http://localhost](http://localhost)
- **Mailpit (メール確認)**: [http://localhost:8025](http://localhost:8025)

### テストユーザー
初期データ（Seed）を投入した場合、以下のユーザーでログイン可能です。
- **Email**: `test@example.com`
- **Password**: `password`

---

## 主なディレクトリ構成
```text
.
├── docker-compose.yml   # Docker全体の構成管理
├── backend/             # Laravel バックエンド
│   ├── app/             # ビジネスロジック (Controllers, Models)
│   ├── database/        # マイグレーション, シーダー
│   └── routes/          # APIルート定義
└── frontend/            # Vue.js フロントエンド
    ├── src/
    │   ├── components/  # 再利用可能なUIコンポーネント
    │   ├── composables/ # ビジネスロジック (Composition API)
    │   └── api/         # Axios設定・API通信
    └── Dockerfile       # フロントエンド用Dockerイメージ
```
