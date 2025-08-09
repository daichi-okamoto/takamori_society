<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGalleries extends ListRecords
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // ここは protected のままでOK（エラーは出ていないが、publicでも可）
    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\GalleryGrid::class,
        ];
    }

    // ★ public に変更（エラーの本体）
    public function getFooterWidgetsColumns(): int|array
    {
        return 1; // 全幅1カラム
    }
}
