<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1) まず最初にプロキシを信頼（Cloudflare/Koyeb向け）
        $middleware->trustProxies(
            at: '*',  // 特定IPを列挙してもOKだが、まずは '*' で
            headers: Request::HEADER_X_FORWARDED_ALL
        );

        // 2) web グループ（現状のままでOK）
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // 3) ★CSRF 例外は外してOK（署名401には関係なし、セキュリティ的にも不要）
        // $middleware->validateCsrfTokens(except: [
        //     'livewire/upload-file',
        //     'livewire/preview-file/*',
        // ]);
    })
    ->create();
