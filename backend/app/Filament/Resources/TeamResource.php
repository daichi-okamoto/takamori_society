<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('チーム名')
                    ->required()
                    ->maxLength(255),

                // 代表者選択（usersテーブルのnameを表示）
                Select::make('leader_id')
                    ->label('代表者')
                    ->relationship('leader', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) =>
                        $set('leader_email', \App\Models\User::find($state)?->email)
                    ),

                TextInput::make('leader_email')
                    ->label('メールアドレス')
                    ->disabled()
                    ->dehydrated(false)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('チーム名')
                    ->sortable()
                    ->searchable(),

                // 代表者名を表示
                TextColumn::make('leader.name')
                    ->label('代表者')
                    ->sortable()
                    ->searchable(),

                // 連絡先 (メールアドレス) を表示
                TextColumn::make('leader.email')
                    ->label('連絡先')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'チーム管理';
    }

    public static function getModelLabel(): string
    {
        return 'チーム';
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\TeamResource\RelationManagers\PlayersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
            'view' => Pages\ViewTeam::route('/{record}'),
        ];
    }
}
