<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Filament\Resources\GalleryResource\RelationManagers;
use App\Models\Gallery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;
    protected static ?string $navigationLabel = 'ギャラリー';
    protected static ?string $navigationGroup = 'コンテンツ';
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    // 単数/複数のモデル名（ページの標準タイトルなどに使われる）
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
            Forms\Components\Select::make('tournament_id')
                ->label('大会')
                ->relationship('tournament', 'name')
                ->required(),

            Forms\Components\FileUpload::make('image_url')
                ->label('画像')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                ->directory('galleries')
                ->multiple()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('画像')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('大会')
                    ->default('未設定'),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('投稿者')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('投稿日')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit' => Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
