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
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile|string $file, callable $get) {
                        $disk = Storage::disk('r2');
                        $dir  = 'galleries/' . $get('tournament_id');

                        try {
                            $ext = 'jpg';
                            $binary = null;

                            if ($file instanceof TemporaryUploadedFile && is_file($file->getRealPath())) {
                                // ローカル tmp 経由（Intervention はファイル/バイナリOK）
                                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                                $image = Image::read($file->getRealPath())->scaleDown(width: 2000);
                            } else {
                                // R2 の一時キー（文字列）経由
                                $tmpKey = is_string($file) ? $file : $file->getFilename(); // 念のため
                                // まずメタが取れれば拡張子の判断に使う（なくても進む）
                                try {
                                    $mimeGuess = $disk->mimeType($tmpKey);
                                    if (is_string($mimeGuess) && str_contains($mimeGuess, '/')) {
                                        $ext = match (strtolower(explode('/', $mimeGuess)[1])) {
                                            'png' => 'png',
                                            'webp' => 'webp',
                                            default => 'jpg',
                                        };
                                    }
                                } catch (\Throwable $e) {
                                    // 取れなくてもOK。デフォルト jpg で続行
                                }

                                $binaryTmp = $disk->get($tmpKey); // バイナリ取得
                                $image = Image::read($binaryTmp)->scaleDown(width: 2000);
                            }

                            // 出力形式を選択（元拡張子に寄せるが、webp を優先したい場合はここで固定してもOK）
                            $quality = 85;
                            $mime = 'image/jpeg';
                            switch ($ext) {
                                case 'png':
                                    $binary = (string) $image->toPng();
                                    $mime = 'image/png';
                                    break;
                                case 'webp':
                                    $binary = (string) $image->toWebp($quality);
                                    $mime = 'image/webp';
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                default:
                                    $binary = (string) $image->toJpeg($quality);
                                    $mime = 'image/jpeg';
                                    $ext = 'jpg';
                                    break;
                            }

                            $final = $dir . '/' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $ext;

                            // R2 に保存（公開想定）
                            $disk->put($final, $binary, [
                                'visibility'  => 'public',
                                'ContentType' => $mime,
                            ]);

                            // 文字列キーで来ている場合は livewire-tmp を掃除（任意）
                            if (isset($tmpKey) && is_string($tmpKey)) {
                                $disk->delete($tmpKey);
                            }

                            // FileUpload の state にはこのパス（R2キー）を返す
                            return $final;

                        } catch (\Throwable $e) {
                            \Log::error('[Gallery Upload] failed', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                                'size'  => $file instanceof TemporaryUploadedFile ? $file->getSize() : null,
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
