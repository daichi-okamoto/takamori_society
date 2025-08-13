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

# パッケージマネージャに応じてロックファイルを拾う
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./

# 依存をインストール（ロックファイルがあれば厳密インストール）
RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi

# ビルドに必要なファイルだけコピー（無駄なキャッシュを避ける）
COPY resources ./resources
COPY vite.config.* ./
COPY tailwind.config.* postcss.config.* ./

# 本番ビルド（public/build に成果物ができる）
RUN npm run build

# ─────────────────────────────────────────────
# 2) 実行環境：FrankenPHP（PHP + Caddy）
# ─────────────────────────────────────────────
FROM dunglas/frankenphp:latest

# 必要な PHP 拡張
RUN install-php-extensions \
    gd intl pcntl bcmath exif opcache zip \
    pdo_pgsql

WORKDIR /app

# 先に vendor（キャッシュが効く）
COPY --from=vendor /app/vendor /app/vendor
# アプリ本体
COPY . /app
# Vite の成果物を配置（アプリの CSS/JS）
COPY --from=assets /app/public/build /app/public/build

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

# ビルド時にアプリキャッシュは作らない（古い .env を焼き込まない）
# RUN php artisan package:discover || true
# RUN php artisan config:cache    || true
# RUN php artisan route:cache     || true
# RUN php artisan view:cache      || true

# Koyeb の $PORT で待受け（Serviceの公開ポートは 8000 でもOK。Koyeb→PORTに転送される）
ENV SERVER_NAME=:${PORT:-3000}

# worker モードは使わない
# ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# 起動：キャッシュ掃除 → Filament のアセット出力 → サーバ起動
# ※ filament:assets は v3 では --force オプション無し
CMD php artisan optimize:clear >/dev/null 2>&1 || true \
 && php artisan filament:assets || true \
 && frankenphp run --config /etc/caddy/Caddyfile

# 実行権限付与（Dockerfile内）
COPY docker/entrypoint.sh /app/docker/entrypoint.sh
RUN chmod +x /app/docker/entrypoint.sh
