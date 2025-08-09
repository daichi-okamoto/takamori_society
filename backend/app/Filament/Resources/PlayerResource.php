<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerResource\Pages;
use App\Models\Player;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- ユーザーテーブル更新 ---
            Forms\Components\TextInput::make('user.name')
                ->label('名前')
                ->required()
                ->afterStateHydrated(fn ($component, $record) => 
                    $component->state($record->user?->name)
                )
                ->dehydrated(true),

            Forms\Components\TextInput::make('user.kana')
                ->label('フリガナ')
                ->required()
                ->afterStateHydrated(fn ($component, $record) => 
                    $component->state($record->user?->kana)
                )
                ->dehydrated(true),

            // --- Playerテーブル更新 ---
            Forms\Components\TextInput::make('address')
                ->label('住所')
                ->required(),

            Forms\Components\DatePicker::make('date_of_birth')
                ->label('生年月日')
                ->required(),

            Forms\Components\Select::make('teams')
                ->label('所属チーム')
                ->multiple()
                ->relationship('teams', 'name')
                ->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('名前'),
                Tables\Columns\TextColumn::make('user.kana')->label('フリガナ'),
                Tables\Columns\TextColumn::make('address')->label('住所'),
                Tables\Columns\TextColumn::make('date_of_birth')->label('生年月日')->date(),
                Tables\Columns\TextColumn::make('teams.name')
                    ->label('所属チーム')
                    ->badge()
                    ->separator('、'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teams')
                    ->label('所属チーム')
                    ->relationship('teams', 'name')
                    ->multiple(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record) {
                        // Userテーブルの更新
                        $record->user->update([
                            'name' => $data['user.name'] ?? $record->user->name,
                            'kana' => $data['user.kana'] ?? $record->user->kana,
                        ]);
                        return $data;
                    }),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayers::route('/'),
            'create' => Pages\CreatePlayer::route('/create'),
            'edit' => Pages\EditPlayer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return '選手管理';
    }

    public static function getModelLabel(): string
    {
        return '選手';
    }
}
