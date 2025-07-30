<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;

class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';
    protected static ?string $recordTitleAttribute = 'name'; // チーム名をタイトルに

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('チーム名'),

                Forms\Components\TextInput::make('representative')
                    ->label('代表者'),

                Forms\Components\Select::make('group_id')
                    ->relationship('group', 'name')
                    ->label('所属グループ')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('チーム名')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('leader.name')
                    ->label('代表者'),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('所属グループ'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(), // チーム追加
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
