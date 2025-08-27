<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
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
        if (app()->environment('production')) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));  // ★ 追加：Host/Port を固定
        }
        // ★ Livewire のミドルウェアを明示的に固定（auth 等が混ざる事故を防ぐ）
        if (method_exists(Livewire::class, 'setUpdateRouteMiddleware')) {
            Livewire::setUpdateRouteMiddleware(['web']);
        }
        if (method_exists(Livewire::class, 'setFileUploadRouteMiddleware')) {
            Livewire::setFileUploadRouteMiddleware(['web']);
        }
    }
}
