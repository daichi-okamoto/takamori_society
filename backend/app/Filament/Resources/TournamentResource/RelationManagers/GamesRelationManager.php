<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;

class GamesRelationManager extends RelationManager
{
    protected static string $relationship = 'games';
    protected static ?string $recordTitleAttribute = 'id'; // 試合IDをタイトルに

    public function form(Form $form): Form
    {
    return $form
        ->schema([
            Forms\Components\Select::make('group_id')
                ->relationship('group', 'name')
                ->label('所属グループ')
                ->required(),

            Forms\Components\Select::make('team_a_id')
                ->relationship('teamA', 'name')
                ->required()
                ->label('チームA'),

            Forms\Components\Select::make('team_b_id')
                ->relationship('teamB', 'name')
                ->required()
                ->label('チームB'),

            Forms\Components\DateTimePicker::make('match_date')
                ->label('試合日時')
                ->required(),

            Forms\Components\Select::make('status')
                ->label('試合ステータス')
                ->options([
                    'scheduled' => '予定',
                    'ongoing'   => '進行中',
                    'finished'  => '終了',
                ])
                ->required(),

            Forms\Components\TextInput::make('team_a_score')
                ->numeric()
                ->label('チームAスコア')
                ->visible(fn (callable $get) => $get('status') === 'finished'),

            Forms\Components\TextInput::make('team_b_score')
                ->numeric()
                ->label('チームBスコア')
                ->visible(fn (callable $get) => $get('status') === 'finished'),
        ]);
    }


    public function table(Table $table): Table
    {
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('match_card')->label('対戦カード'),

            Tables\Columns\TextColumn::make('match_result')->label('結果'),

            Tables\Columns\TextColumn::make('match_date')->dateTime('Y-m-d H:i')->label('試合日時'),
            
            Tables\Columns\TextColumn::make('group.name')
                ->label('グループ')
                ->sortable()
                ->searchable(),
        ])
        ->defaultSort('match_date', 'asc')
        ->headerActions([
            Tables\Actions\CreateAction::make(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
