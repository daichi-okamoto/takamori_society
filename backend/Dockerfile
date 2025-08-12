# ─────────────────────────────────────────────
# 1) vendor ステージ（composer だけの軽量ステージ）
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

# 必要な PHP 拡張
RUN install-php-extensions \
    gd intl pcntl bcmath exif opcache zip \
    pdo_pgsql

WORKDIR /app

# 先に vendor を配置（キャッシュ効く）
COPY --from=vendor /app/vendor /app/vendor
# アプリ本体をコピー
COPY . /app

# Caddy のデータディレクトリ（pki の権限エラー回避）
RUN mkdir -p /data/caddy && chown -R www-data:www-data /data

# Caddy/FrankenPHP のデータをアプリ配下に寄せる（任意）
ENV XDG_DATA_HOME=/app/storage
ENV XDG_CONFIG_HOME=/app/storage

# Laravel 用の権限
RUN mkdir -p /app/storage /app/bootstrap/cache /app/public \
 && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public

# 実行ユーザー
USER www-data

# ⚠️ ビルド時キャッシュは作らない（古い .env を焼き込まないため）
# RUN php artisan package:discover || true
# RUN php artisan config:cache    || true
# RUN php artisan route:cache     || true
# RUN php artisan view:cache      || true

# Koyeb の $PORT で待受け
ENV SERVER_NAME=:${PORT:-3000}

# ⚠️ worker モードは使わない（通常のPHP実行でOK）
# ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# 起動コマンド：まずキャッシュを掃除してからサーバ起動
# ※ マイグレーションは手動で 1 回だけ実行してください（デプロイ後に）
CMD php artisan optimize:clear >/dev/null 2>&1 || true \
 && frankenphp run --config /etc/caddy/Caddyfile