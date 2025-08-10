# 1) ビルド用（Composerで依存解決）
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# 2) ランタイム（FrankenPHP は PHP + Caddy = 高速/省メモリ）
FROM dunglas/frankenphp:latest

# 必要拡張（pgsql, pdo_pgsql, gd, zip など。MySQLなら pdo_mysql に変更）
RUN install-php-extensions \
    gd intl pcntl bcmath exif opcache zip \
    pdo_pgsql

# 作業ディレクトリ
WORKDIR /app

# アプリ本体
COPY . /app

# ベンダー(ビルド済み) を上書きコピー
COPY --from=vendor /app/vendor /app/vendor

# 権限（storage と bootstrap/cache）
RUN addgroup --system laravel && adduser --system --ingroup laravel laravel \
 && chown -R laravel:laravel /app/storage /app/bootstrap/cache

USER laravel

# 本番キャッシュ（存在しなくてもOK）
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache  || true

# Laravel の公開ディレクトリを root に設定
ENV SERVER_NAME=:${PORT:-3000}
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# 起動時にストレージリンクとマイグレーション実行（安全に）
CMD php artisan storage:link || true \
 && php artisan migrate --force || true \
 && frankenphp run --config /etc/caddy/Caddyfile
