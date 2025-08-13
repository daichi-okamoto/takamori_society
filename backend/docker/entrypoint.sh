#!/bin/sh
set -e

# 1度だけマイグレーション（Koyeb の環境変数 RUN_MIGRATIONS=1 をセットして再デプロイ）
if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
  echo "[entrypoint] Running migrations..."
  php artisan migrate --force
  echo "[entrypoint] Migrations done."
fi

# 通常の起動準備
php artisan optimize:clear >/dev/null 2>&1 || true
php artisan filament:assets || true

# FrankenPHP 起動
exec frankenphp run --config /etc/caddy/Caddyfile
