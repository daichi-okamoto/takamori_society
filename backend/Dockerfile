# ── 1) Composer で vendor を用意（ビルド段階）
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction \
    --ignore-platform-req=ext-intl

# ── 2) 実行環境：FrankenPHP（PHP + Caddy）
FROM dunglas/frankenphp:latest

# 必要PHP拡張をインストール
# もし MySQL を使うなら pdo_pgsql を pdo_mysql に変更してください
RUN install-php-extensions \
    gd intl pcntl bcmath exif opcache zip \
    pdo_pgsql

WORKDIR /app

# アプリ本体
COPY . /app
# vendor を上書きコピー
COPY --from=vendor /app/vendor /app/vendor

# 権限（storage と bootstrap/cache）
RUN addgroup --system laravel && adduser --system --ingroup laravel laravel \
 && chown -R laravel:laravel /app/storage /app/bootstrap/cache
USER laravel

# キャッシュ（環境変数が未設定でも失敗しないように || true）
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache  || true

# Koyeb が割り当てる $PORT で待ち受け
ENV SERVER_NAME=:${PORT:-3000}
# FrankenPHP にエントリポイント（public/index.php）を指定
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# 起動時：storage:link と migrate 実行 → Webサーバ起動
CMD php artisan storage:link || true \
 && php artisan migrate --force || true \
 && frankenphp run --config /etc/caddy/Caddyfile
