<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PlayersRelationManager extends RelationManager
{
    protected static string $relationship = 'players';
    protected static ?string $title = 'チームメンバー';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // 選手の名前
                Forms\Components\TextInput::make('name')
                    ->label('名前')
                    ->required()
                    ->maxLength(255),

                // フリガナ
                Forms\Components\TextInput::make('kana')
                    ->label('フリガナ')
                    ->maxLength(255),

                // 住所
                Forms\Components\TextInput::make('address')
                    ->label('住所')
                    ->maxLength(255),

                // 連絡先（ユーザーのメール）
                Forms\Components\TextInput::make('user.email')
                    ->label('メールアドレス')
                    ->disabled() // 編集不可
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('名前')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.kana')
                    ->label('フリガナ')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('メールアドレス')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('住所')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('メンバー追加'),
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
}
