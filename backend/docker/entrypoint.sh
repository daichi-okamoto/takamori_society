#!/usr/bin/env bash
set -euo pipefail

cd /app

echo "[entrypoint] preparing writable dirs..."

# ── 必須ディレクトリを毎回生成（Livewire 一時アップロード含む）
mkdir -p storage/framework/{cache,sessions,testing,views,livewire-tmp}
mkdir -p bootstrap/cache
mkdir -p public

# ── 実行ユーザーに合わせて所有権＆権限を調整
# Koyeb ではコンテナ内 USER=www-data（Dockerfileで設定）なので id -u/-g で安全に合わせる
chown -R "$(id -u)":"$(id -g)" storage bootstrap/cache public || true
chmod -R ug+rwX storage bootstrap/cache public

# ── PHP アップロード制限（将来のため任意。環境変数で上書き可）
#     UPLOAD_MAX_FILESIZE=10M / POST_MAX_SIZE=12M など
{
  echo "upload_max_filesize=${UPLOAD_MAX_FILESIZE:-10M}"
  echo "post_max_size=${POST_MAX_SIZE:-12M}"
} > /usr/local/etc/php/conf.d/uploads.ini || true

# ── storage:link は何度叩いても安全
php artisan storage:link >/dev/null 2>&1 || true

# キャッシュは安全なものだけ（route:cache はクロージャがあると失敗するためデフォルトOFF）
php artisan optimize:clear >/dev/null 2>&1 || true
if [[ "${LARAVEL_CONFIG_CACHE:-1}" = "1" ]]; then php artisan config:cache || true; fi
if [[ "${LARAVEL_VIEW_CACHE:-1}" = "1" ]]; then php artisan view:cache   || true; fi
if [[ "${LARAVEL_ROUTE_CACHE:-0}" = "1" ]]; then php artisan route:cache  || true; fi

# Filament の公開アセット（上書き許可）
php artisan filament:assets --force || true

# ── マイグレーション（必要時のみ）
if [[ "${RUN_MIGRATIONS:-0}" = "1" ]]; then
  echo "[entrypoint] Running migrations..."
  php artisan migrate --force --no-interaction
  echo "[entrypoint] Migrations done."
fi

# ──（任意）権限の実チェックをログ出力
echo "[entrypoint] writable check: framework=$(php -r 'echo is_writable(\"storage/framework\")?\"1\":\"0\";') tmp=$(php -r 'echo is_writable(\"storage/framework/livewire-tmp\")?\"1\":\"0\";') cache=$(php -r 'echo is_writable(\"bootstrap/cache\")?\"1\":\"0\";')"

# ── FrankenPHP 起動（Koyeb の $PORT で待受。Dockerfile の SERVER_NAME も設定済み）
exec frankenphp run --config /etc/caddy/Caddyfile
