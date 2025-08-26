<?php

namespace App\Filament\Widgets;

use App\Models\Team;
use App\Models\Player;
use App\Models\Tournament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected ?string $heading = 'サマリー';

    protected function getCards(): array
    {
        return [
            Card::make('登録チーム数', Team::count())
                ->description('現在登録されているチームの数')
                ->color('success')
                ->icon('heroicon-o-users'),

            Card::make('登録選手数', Player::count())
                ->description('現在登録されている選手の数')
                ->color('info')
                ->icon('heroicon-o-user'),

            Card::make('大会数', Tournament::count())
                ->description('これまでに開催された大会数')
                ->color('warning')
                ->icon('heroicon-o-trophy'),
        ];
    }
}
