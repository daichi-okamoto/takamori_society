# ─────────────────────────────────────────────
# 1) vendor ステージ（そのままでOK / だいちの設定を活かす）
# ─────────────────────────────────────────────
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev --prefer-dist --no-progress --no-interaction \
    --no-scripts \
    --ignore-platform-req=ext-intl \
 && rm -rf /root/.composer/cache

# ─────────────────────────────────────────────
# 2) 実行環境：FrankenPHP（PHP + Caddy）
# ─────────────────────────────────────────────
FROM dunglas/frankenphp:latest

# 必要拡張
RUN install-php-extensions gd intl pcntl bcmath exif opcache zip pdo_pgsql

WORKDIR /app

# 先に vendor
COPY --from=vendor /app/vendor /app/vendor
# アプリ本体
COPY . /app

# ここは root のまま
# Caddy が使うデータディレクトリを用意 & 権限付与（どちらか/両方でOK）
RUN mkdir -p /data/caddy && chown -R www-data:www-data /data
# あるいは Caddy のデータ場所をアプリ配下に変更したい場合は環境変数で
ENV XDG_DATA_HOME=/app/storage
ENV XDG_CONFIG_HOME=/app/storage

# Laravel 用の権限
RUN mkdir -p /app/storage /app/bootstrap/cache \
 && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public

# 実行ユーザー
USER www-data

# ビルド時の軽い最適化（失敗しても継続）
RUN php artisan package:discover || true \
 && php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache  || true

# Koyeb の PORT を使う
ENV SERVER_NAME=:${PORT:-3000}
# FrankenPHP のエントリポイント
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# 起動コマンド
# ※ storage:link は R2 運用なら不要なので削除
# ※ migrate は失敗しても起動は続行（DB準備の前後で再起動されるため）
CMD php artisan migrate --force || true \
 && frankenphp run --config /etc/caddy/Caddyfile
