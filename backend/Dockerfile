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

# ここは root のまま
# Caddy のデータディレクトリを用意（pki の権限エラー回避）
RUN mkdir -p /data/caddy && chown -R www-data:www-data /data

# Caddy/FrankenPHP のデータをアプリ配下に寄せる（任意。root権限不要化に寄与）
ENV XDG_DATA_HOME=/app/storage
ENV XDG_CONFIG_HOME=/app/storage

# Laravel 用の権限
RUN mkdir -p /app/storage /app/bootstrap/cache /app/public \
 && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public

# 実行ユーザー
USER www-data

# ⚠️ ビルド時に config:cache しない（古い .env を焼き込むため）
# RUN php artisan package:discover || true
# RUN php artisan config:cache || true
# RUN php artisan route:cache  || true
# RUN php artisan view:cache   || true

# Koyeb の $PORT で待受け
ENV SERVER_NAME=:${PORT:-3000}

# ⚠️ Laravelの index.php は worker モード不要。指定しない。
# ENV FRANKENPHP_CONFIG="worker ./public/index.php"  ←削除

# 起動：古いキャッシュを消してから migrate、最後にサーバ起動
CMD php artisan optimize:clear >/dev/null 2>&1 || true \
 && php artisan migrate --force || true \
 && frankenphp run --config /etc/caddy/Caddyfile