<?php
// app/Filament/Resources/GalleryResource/Pages/CreateGallery.php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Gallery;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): Gallery
    {
        $tournamentId = $data['tournament_id'];
        $uploadedBy   = $data['uploaded_by'] ?? Auth::id();
        $description  = $data['description'] ?? null;
        $disk         = 'r2';

        // FileUpload の戻り値（R2パス配列）
        $paths = $data['images'] ?? [];
        if (is_string($paths)) {
            $paths = [$paths];
        }
        $paths = array_values(array_filter($paths));

        if (empty($paths)) {
            throw ValidationException::withMessages([
                'images' => '画像を少なくとも1枚選択してください。',
            ]);
        }

        $created = null;

        foreach ($paths as $path) {
            $meta = ['mime' => null, 'size' => null];
            if (Storage::disk($disk)->exists($path)) {
                $meta['mime'] = Storage::disk($disk)->mimeType($path);
                $meta['size'] = Storage::disk($disk)->size($path);
            }

            $created = Gallery::create([
                'tournament_id' => $tournamentId,
                'uploaded_by'   => $uploadedBy,
                'description'   => $description,
                'disk'          => $disk,
                'path'          => $path,
                'mime'          => $meta['mime'],
                'size'          => $meta['size'],
                'visibility'    => 'public',
            ]);
        }

        Notification::make()
            ->title('ギャラリーに画像を追加しました')
            ->success()
            ->body(count($paths) . '枚の画像が登録されました。')
            ->send();

        // 必ず作成したレコードを返す
        return $created;
    }
}
