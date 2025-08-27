#!/usr/bin/env bash
set -euo pipefail

cd /app
echo "[entrypoint] preparing writable dirs..."

# 必須ディレクトリ（毎回）
mkdir -p storage/framework/{cache,sessions,testing,views,livewire-tmp}
mkdir -p bootstrap/cache public
chown -R "$(id -u)":"$(id -g)" storage bootstrap/cache public || true
chmod -R ug+rwX storage bootstrap/cache public

# storage:link など
php artisan storage:link >/dev/null 2>&1 || true
php artisan optimize:clear >/dev/null 2>&1 || true
php artisan config:cache || true
php artisan view:cache   || true
php artisan filament:assets || true   # ← --force 削除

# マイグレーション（必要時のみ）
if [[ "${RUN_MIGRATIONS:-0}" = "1" ]]; then
  echo "[entrypoint] Running migrations..."
  php artisan migrate --force --no-interaction || true
  echo "[entrypoint] Migrations done."
fi

# 書き込みチェック（見やすいログ）
echo "[entrypoint] writable check: \
framework=$(php -r "echo is_writable('storage/framework') ? '1' : '0';") \
tmp=$(php -r "echo is_writable('storage/framework/livewire-tmp') ? '1' : '0';") \
cache=$(php -r "echo is_writable('bootstrap/cache') ? '1' : '0';")"

# FrankenPHP 起動
exec frankenphp run --config /etc/caddy/Caddyfile
