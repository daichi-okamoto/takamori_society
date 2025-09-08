<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Models\Gallery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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

                FileUpload::make('images')
                    ->label('画像（複数可）')
                    ->image()
                    ->multiple()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(8 * 1024) // 8MB
                    ->required()
                    ->helperText('最長辺2000pxに縮小してWebP/JPEG/PNGでR2へ保存します')
                    // R2 を使う前提。独自保存でも明示しておくと安心
                    ->disk('r2')
                    ->preserveFilenames(false)
                    /**
                     * Livewire(S3/R2直)の場合、$file が「livewire-tmp/...」という“R2キーの文字列”で渡って来ることがあります。
                     * ローカルtmpがある場合は TemporaryUploadedFile として getRealPath() が使えます。
                     * どちらでも動くように分岐します。
                     */
                ->saveUploadedFileUsing(function (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile|string $file, callable $get) {
                    $finalDisk = Storage::disk('r2');

                    // Livewire の一時アップロード設定（デフォルト: disk=r2, dir=livewire-tmp）
                    $tmpDiskName = config('livewire.temporary_file_upload.disk', 'r2');
                    $tmpDir      = trim(config('livewire.temporary_file_upload.directory', 'livewire-tmp'), '/');
                    $tmpDisk     = Storage::disk($tmpDiskName);

                    $dir  = 'galleries/' . $get('tournament_id');
                    $ext  = 'jpg';
                    $img  = null;

                    try {
                        if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile && is_file($file->getRealPath())) {
                            // ローカル tmp がある（開発環境等）
                            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                            $img = \Intervention\Image\Laravel\Facades\Image::read($file->getRealPath())->scaleDown(width: 2000);
                        } else {
                            // 直アップロード（R2 上の一時キー）
                            // まず“渡された値”から候補キーを組み立てる
                            $key = is_string($file) ? $file : $file->getFilename();      // 多くの場合ファイル名のみ
                            $candidates = [];

                            // そのまま
                            $candidates[] = ltrim($key, '/');
                            // livewire-tmp を前置
                            $candidates[] = $tmpDir . '/' . ltrim($key, '/');

                            // 見つかったキーを採用
                            $tmpKey = null;
                            foreach ($candidates as $cand) {
                                if ($tmpDisk->exists($cand)) { $tmpKey = $cand; break; }
                            }

                            if (!$tmpKey) {
                                \Log::error('[Gallery Upload] temporary key not found on R2', ['tried' => $candidates]);
                                throw new \RuntimeException('Temporary uploaded file cannot be found on R2.');
                            }

                            // MIME から出力拡張子の初期値を推定（失敗しても JPG にフォールバック）
                            try {
                                $mimeGuess = $tmpDisk->mimeType($tmpKey);
                                if (is_string($mimeGuess) && str_contains($mimeGuess, '/')) {
                                    $ext = match (strtolower(explode('/', $mimeGuess)[1])) {
                                        'png'  => 'png',
                                        'webp' => 'webp',
                                        default => 'jpg',
                                    };
                                }
                            } catch (\Throwable $e) { /* ignore */ }

                            // R2 から一時オブジェクトを取得 → Intervention で読み込み
                            $binaryTmp = $tmpDisk->get($tmpKey);
                            $img = \Intervention\Image\Laravel\Facades\Image::read($binaryTmp)->scaleDown(width: 2000);

                            // 後始末（任意）
                            try { $tmpDisk->delete($tmpKey); } catch (\Throwable $e) { /* ignore */ }
                        }

                        // 出力（拡張子で分岐）
                        $quality = 85;
                        $mime = 'image/jpeg';
                        switch ($ext) {
                            case 'png':
                                $binary = (string) $img->toPng();
                                $mime = 'image/png';
                                $ext  = 'png';
                                break;
                            case 'webp':
                                $binary = (string) $img->toWebp($quality);
                                $mime = 'image/webp';
                                $ext  = 'webp';
                                break;
                            default:
                                $binary = (string) $img->toJpeg($quality);
                                $mime = 'image/jpeg';
                                $ext  = 'jpg';
                        }

                        $final = $dir . '/' . now()->format('Ymd_His') . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;

                        $finalDisk->put($final, $binary, [
                            'visibility'  => 'public',
                            'ContentType' => $mime,
                        ]);

                        return $final;

                    } catch (\Throwable $e) {
                        \Log::error('[Gallery Upload] failed', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'size'  => $file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile ? $file->getSize() : null,
                        ]);
                        throw $e;
                    }
                }),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('画像')
                    ->getStateUsing(fn (Gallery $record) => $record->url) // モデル側でURLアクセサを実装している想定
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
