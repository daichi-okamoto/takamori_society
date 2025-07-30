<?php

namespace App\Filament\Resources;

use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\TournamentResource\Pages;

class TournamentResource extends Resource
{
    protected static ?string $model = Tournament::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = '大会管理';
    protected static ?string $pluralModelLabel = '大会';
    protected static ?string $modelLabel = '大会';

    public static function form(Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('大会名'),

                Forms\Components\DatePicker::make('date')
                    ->label('開催日'),

                Forms\Components\TextInput::make('location')
                    ->label('会場')
                    ->placeholder('例：山吹ほたるパークグラウンド'),

                // ✅ 今は一覧では表示しないが編集ページで管理
                Forms\Components\HasManyRepeater::make('groups')
                    ->label('グループ')
                    ->relationship('groups')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('グループ名'),
                    ])
                    ->createItemButtonLabel('グループを追加')
                    ->collapsible()
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('大会名')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('開催日')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('会場')
                    ->sortable()
                    ->searchable(),

                // ✅ 参加チーム数を表示
                Tables\Columns\TextColumn::make('teams_count')
                    ->counts('teams')
                    ->label('参加チーム数'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('作成日'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTournaments::route('/'),
            'create' => Pages\CreateTournament::route('/create'),
            'edit' => Pages\EditTournament::route('/{record}/edit'),
            'view' => Pages\ViewTournament::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\TournamentResource\RelationManagers\GroupsRelationManager::class,
            \App\Filament\Resources\TournamentResource\RelationManagers\TeamsRelationManager::class,
            \App\Filament\Resources\TournamentResource\RelationManagers\GamesRelationManager::class,
        ];
    }
}
