# ─────────────────────────────────────────────
# 1) vendor を用意（composer だけの軽量ステージ）
# ─────────────────────────────────────────────
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
# ← ここで --no-scripts を付けて、artisan 不在でも失敗しないようにする
RUN composer install \
    --no-dev --prefer-dist --no-progress --no-interaction \
    --no-scripts \
    --ignore-platform-req=ext-intl \
 && rm -rf /root/.composer/cache

# ─────────────────────────────────────────────
# 2) 実行環境：FrankenPHP（PHP + Caddy）
# ─────────────────────────────────────────────
FROM dunglas/frankenphp:latest

# 必要な PHP 拡張をインストール
# ※ MySQL を使う場合は pdo_pgsql を pdo_mysql に変更
RUN install-php-extensions \
    gd intl pcntl bcmath exif opcache zip \
    pdo_pgsql

WORKDIR /app

# 先に vendor を配置（キャッシュ効く）
COPY --from=vendor /app/vendor /app/vendor
# アプリ本体をコピー（artisan を含む）
COPY . /app

# 権限調整（storage と bootstrap/cache）
RUN addgroup --system laravel && adduser --system --ingroup laravel laravel \
 && mkdir -p /app/storage /app/bootstrap/cache \
 && chown -R laravel:laravel /app/storage /app/bootstrap/cache
USER laravel

# ビルド時の最小最適化（環境未設定でも落ちないように || true）
RUN php artisan package:discover || true \
 && php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache  || true

# Koyeb の $PORT で待受け（ローカルは 3000）
ENV SERVER_NAME=:${PORT:-3000}
# FrankenPHP のエントリポイント（public/index.php）
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# 起動：storage:link / migrate 実行後に Web サーバ起動
CMD php artisan storage:link || true \
 && php artisan migrate --force || true \
 && frankenphp run --config /etc/caddy/Caddyfile
