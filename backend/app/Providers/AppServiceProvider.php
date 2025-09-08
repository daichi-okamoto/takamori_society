<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 本番では https と APP_URL に固定して署名URLの不一致を防ぐ
        if (app()->isProduction()) {
            URL::forceScheme('https');
            // APP_URL（.envのAPP_URL）を絶対的な基準にする
            URL::forceRootUrl(config('app.url'));
        }

        // Livewire のルートには web ミドルウェアのみを適用
        if (method_exists(Livewire::class, 'setUpdateRouteMiddleware')) {
            Livewire::setUpdateRouteMiddleware(['web']);
        }
        if (method_exists(Livewire::class, 'setFileUploadRouteMiddleware')) {
            Livewire::setFileUploadRouteMiddleware(['web']);
        }
    }
}
