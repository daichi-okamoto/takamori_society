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
# 1.5) assets ステージ（Vite で CSS/JS を本番ビルド）
# ─────────────────────────────────────────────
FROM node:20-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./

RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi

COPY resources ./resources
COPY vite.config.* ./
COPY tailwind.config.* postcss.config.* ./
RUN npm run build

# ─────────────────────────────────────────────
# 2) 実行環境：FrankenPHP（PHP + Caddy）
# ─────────────────────────────────────────────
FROM dunglas/frankenphp:latest

# 必要拡張を導入（R2はS3互換なので ext-pdo_pgsql なども）
RUN install-php-extensions \
    gd intl pcntl bcmath exif opcache zip \
    pdo_pgsql

WORKDIR /app

# 先に vendor（キャッシュ効率UP）
COPY --from=vendor /app/vendor /app/vendor

# アプリ本体
COPY . /app

# Vite ビルド成果物
COPY --from=assets /app/public/build /app/public/build

# entrypoint を配置
COPY --chmod=0755 docker/entrypoint.sh /app/docker/entrypoint.sh

# Caddy のデータディレクトリを用意
RUN mkdir -p /data/caddy && chown -R www-data:www-data /data

# Caddy/FrankenPHP のワークディレクトリをアプリ配下へ（任意）
ENV XDG_DATA_HOME=/app/storage
ENV XDG_CONFIG_HOME=/app/storage

# Laravel 用の初期ディレクトリと所有権（ビルド時点）
RUN mkdir -p /app/storage /app/bootstrap/cache /app/public \
 && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public \
 && chown www-data:www-data /app/docker/entrypoint.sh

 # Dockerfile
RUN mkdir -p /usr/local/etc/php/conf.d \
 && printf "upload_max_filesize=20M\npost_max_size=21M\n" \
    > /usr/local/etc/php/conf.d/uploads.ini

# 実行ユーザー
USER www-data

# Koyeb の $PORT で待受（Caddy の SERVER_NAME 指定）
ENV SERVER_NAME=:${PORT:-3000}

# 起動は entrypoint に委譲
CMD ["/app/docker/entrypoint.sh"]
