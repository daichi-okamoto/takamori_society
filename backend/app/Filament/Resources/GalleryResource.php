<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Models\Gallery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// 追加 use
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static ?string $navigationLabel = 'ギャラリー';
    protected static ?string $navigationGroup = 'コンテンツ';
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function getModelLabel(): string
    {
        return 'ギャラリー';
    }

    public static function getPluralModelLabel(): string
    {
        return 'ギャラリー';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(1)->schema([
                Select::make('tournament_id')
                    ->label('大会')
                    ->relationship('tournament', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                // 🔽 DBに保存しない一時フィールド。圧縮→R2保存して「保存済みパス」を返す
                FileUpload::make('images')
                    ->label('画像（複数可）')
                    ->image()
                    ->multiple()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(8 * 1024) // 8MB
                    ->required() // ← 任意（必須にするなら）
                    ->helperText('最長辺 2000px・JPEG/WEBP 圧縮でR2へ保存します')
                    // ここでは disk() を使わず、下の saveUploadedFileUsing で自前保存
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, callable $get) {
                        $disk = 'r2';
                        $dir  = "galleries/{$get('tournament_id')}";
                        $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                        $name = now()->format('Ymd_His') . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;

                        $maxSide = 2000;
                        $quality = 85;

                        $img = \Intervention\Image\Laravel\Facades\Image::read($file->getRealPath())->scaleDown($maxSide);

                        switch ($ext) {
                            case 'png':
                                $binary = (string) $img->toPng();
                                $mime = 'image/png';
                                break;
                            case 'webp':
                                $binary = (string) $img->toWebp($quality);
                                $mime = 'image/webp';
                                break;
                            case 'jpg':
                            case 'jpeg':
                            default:
                                $binary = (string) $img->toJpeg($quality);
                                $mime = 'image/jpeg';
                                break;
                        }

                        $path = "$dir/$name";

                        \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $binary, [
                            'visibility'  => 'public',
                            'ContentType' => $mime,
                        ]);

                        return $path;
                    })

            ]),
        ]);
    }

// App\Filament\Resources\GalleryResource::table()

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('画像')
                    // レコードのURLアクセサを使って常にフルURLを返す
                    ->getStateUsing(fn (Gallery $record) => $record->url)
                    ->square()
                    ->size(84),

                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('大会')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('投稿者')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('投稿日')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // 追加のRelationManagerがあればここに
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit'   => Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
