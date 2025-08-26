<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('チーム名')
                ->required()
                ->maxLength(255),

            // 代表者は編集時のみ表示（新規時は非表示）
            Select::make('leader_id')
                ->label('代表者')
                ->searchable()
                ->preload()
                ->nullable()
                ->visibleOn('edit') // ★ 作成時は非表示
                ->helperText('このチームのメンバーの中から選択（ユーザー登録済みの選手のみ）')
                ->options(function ($record) {
                    if (! $record?->id) return [];
                    return User::query()
                        ->whereIn('id', function ($q) use ($record) {
                            $q->from('players')
                            ->join('player_team', 'player_team.player_id', '=', 'players.id')
                            ->where('player_team.team_id', $record->id)
                            ->whereNotNull('players.user_id')
                            ->select('players.user_id');
                        })
                        ->orderBy('name')
                        ->pluck('name', 'id');
                }),
                // ※ ここにクロージャrulesは付けない（新規時に解決できず落ちるため）
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
                
                TextColumn::make('leader.phone')
                    ->label('電話番号')
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
