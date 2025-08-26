<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * ダッシュボード上部（StatsOverview など）に表示するウィジェット
     */
    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\AgeStats::class,      // 追加
            \App\Filament\Widgets\StatsOverview::class,
        ];
    }

    /**
     * ダッシュボード本文に表示するウィジェット
     */
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AgeDistributionChart::class, // 追加
        ];
    }

    /**
     * レイアウトカラム設定
     */
    public function getColumns(): int|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
