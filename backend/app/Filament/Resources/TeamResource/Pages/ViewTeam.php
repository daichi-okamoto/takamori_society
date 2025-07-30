<?php

namespace App\Filament\Resources\TeamResource\Pages;

use App\Filament\Resources\TeamResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;

class ViewTeam extends ViewRecord
{
    protected static string $resource = TeamResource::class;

    /**
     * 詳細ページで表示する項目
     */
    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name')
                    ->label('チーム名'),

                TextEntry::make('leader.name')
                    ->label('代表者'),

                TextEntry::make('leader.phone')
                    ->label('連絡先'),

                TextEntry::make('group.tournament.name')
                    ->label('参加大会')
                    ->default('未登録'),

                TextEntry::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->label('登録日'),

                TextEntry::make('updated_at')
                    ->dateTime('Y-m-d H:i')
                    ->label('更新日'),
            ]);
    }
}
