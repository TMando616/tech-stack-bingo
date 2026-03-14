FROM node:22-alpine

WORKDIR /app

# 依存関係をコピー
COPY package*.json ./

# インストール
RUN npm install

# アプリケーションコードをコピー
COPY . .

# Viteの開発サーバー用ポート
EXPOSE 5173

# Viteを起動 (ネットワーク経由でアクセス可能なように --host を付与)
CMD ["npm", "run", "dev", "--", "--host"]
