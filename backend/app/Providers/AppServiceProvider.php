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
        if (app()->isProduction()) {
            // 署名URLを常に https & 実ホストで生成
            URL::forceScheme('https');
            if (request()->hasHeader('host')) {
                // ← ここがポイント：実際に来た Host をそのまま使う
                URL::forceRootUrl('https://' . request()->getHttpHost());
            }
        }

        // Livewire のルートには web ミドルウェアのみ
        if (method_exists(\Livewire\Livewire::class, 'setUpdateRouteMiddleware')) {
            \Livewire\Livewire::setUpdateRouteMiddleware(['web']);
        }
        if (method_exists(\Livewire\Livewire::class, 'setFileUploadRouteMiddleware')) {
            \Livewire\Livewire::setFileUploadRouteMiddleware(['web']);
        }
    }
}
