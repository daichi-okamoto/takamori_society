<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Team;

class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';
    protected static ?string $title = 'チーム';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('チーム名'),

                Forms\Components\TextInput::make('representative')
                    ->label('代表者'),
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
                Tables\Columns\TextColumn::make('leader.email')
                    ->label('連絡先'),
                Tables\Columns\TextColumn::make('pivot.group_id')
                    ->label('所属グループ')
                    ->formatStateUsing(function ($state) {
                        return optional(\App\Models\Group::find($state))->name ?? '未設定';
                    }),
            ])
            ->headerActions([
                // 新規作成して大会に紐付け
                Tables\Actions\CreateAction::make()
                    ->label('新しいチームを作成')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('チーム名'),
                        Forms\Components\TextInput::make('representative')
                            ->label('代表者'),
                        Forms\Components\Select::make('group_id')
                            ->label('所属グループ')
                            ->options(
                                $this->ownerRecord->groups()->pluck('name', 'id')
                            )
                            ->required(),
                    ])
                    ->using(function (array $data) {
                        $team = Team::create([
                            'name' => $data['name'],
                            'leader_id' => null, // 必要なら代表者を別テーブルで管理
                        ]);

                        // pivot に登録
                        $this->ownerRecord->teams()->attach($team->id, [
                            'group_id' => $data['group_id'],
                        ]);

                        return $team;
                    }),

                // 既存チームを追加
                Tables\Actions\AttachAction::make()
                    ->label('既存チームを追加')
                    ->preloadRecordSelect()
                    ->form([
                        Forms\Components\Select::make('team_id')
                            ->label('チームを選択')
                            ->options(
                                \App\Models\Team::pluck('name', 'id') // 登録済みチーム一覧
                            )
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('group_id')
                            ->label('所属グループ')
                            ->options(
                                $this->ownerRecord->groups()->pluck('name', 'id')
                            )
                            ->required(),
                    ])
                    ->action(function ($data) {
                        $this->ownerRecord->teams()->attach($data['team_id'], [
                            'group_id' => $data['group_id'],
                        ]);
                    })
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('大会から外す'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()->label('一括外す'),
            ]);
    }
}
