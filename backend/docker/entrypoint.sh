#!/bin/sh
set -e

cd /app

# 1度だけマイグレーション（Koyeb の環境変数 RUN_MIGRATIONS=1 をセットして再デプロイ）
if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
  echo "[entrypoint] Running migrations..."
  # ログを詳細に見たい場合は次の行のコメントを外す
  # set -x
  php artisan migrate --force --no-interaction
  # set +x
  echo "[entrypoint] Migrations done."
fi

# 必要なら一度だけストレージシンボリックリンク（publicディスクを使う場合）
if [ ! -e public/storage ]; then
  php artisan storage:link >/dev/null 2>&1 || true
fi

# 通常の起動準備
php artisan optimize:clear >/dev/null 2>&1 || true
php artisan filament:assets || true

# FrankenPHP 起動
exec frankenphp run --config /etc/caddy/Caddyfile
