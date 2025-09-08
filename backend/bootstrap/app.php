<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ── Cloudflare / Koyeb 向け: X-Forwarded-* を信頼
        // Symfony の定数はバージョンで差があるため、存在チェックして柔軟に組み立てる
        $headers = \defined('\Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_ALL')
            ? SymfonyRequest::HEADER_X_FORWARDED_ALL
            : (
                SymfonyRequest::HEADER_X_FORWARDED_FOR
                | SymfonyRequest::HEADER_X_FORWARDED_HOST
                | SymfonyRequest::HEADER_X_FORWARDED_PROTO
                | SymfonyRequest::HEADER_X_FORWARDED_PORT
                | (\defined('\Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PREFIX')
                    ? SymfonyRequest::HEADER_X_FORWARDED_PREFIX
                    : 0)
            );

        $middleware->trustProxies(
            at: '*',
            headers: $headers
        );

        // web グループ（必要最低限）
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // CSRF 例外は不要（Livewire 署名 401 とは無関係）
        // $middleware->validateCsrfTokens(except: [
        //     'livewire/upload-file',
        //     'livewire/preview-file/*',
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
