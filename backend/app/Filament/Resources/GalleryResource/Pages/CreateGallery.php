<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Gallery;
use Filament\Notifications\Notification;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 投稿者IDをセット
        $data['uploaded_by'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): Gallery
    {
        $images = $data['image_url'] ?? [];

        $tournamentId = $data['tournament_id']; // ← 確実にここで取得
        $uploadedBy   = $data['uploaded_by'] ?? Auth::id();
        $description  = $data['description'] ?? null;

        // image_url をループで使うのでここで削除
        unset($data['image_url']);

        foreach ($images as $imagePath) {
            Gallery::create([
                'tournament_id' => $tournamentId,
                'uploaded_by'   => $uploadedBy,
                'description'   => $description,
                'image_url'     => $imagePath,
            ]);
        }

        // 通知
        Notification::make()
            ->title('ギャラリーに画像を追加しました')
            ->success()
            ->body(count($images) . '枚の画像が登録されました。')
            ->send();

        return Gallery::latest()->first(); // ← 最後に登録された1件でOK
    }
}
