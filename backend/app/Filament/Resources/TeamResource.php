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

            // ★ このチームのメンバー(=players.user_id)だけから選ぶ
            Select::make('leader_id')
                ->label('代表者')
                ->searchable()
                ->preload()
                ->nullable() // ← 解除できるように null 許可
                ->helperText('このチームのメンバーの中から選択（ユーザー登録済みの選手のみ）')
                ->options(function ($record) {
                    if (! $record?->id) {
                        return []; // 新規作成時はメンバーがまだいない想定
                    }
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
                })
                // バリデーション：メンバー以外は弾く（サーバー側で二重防御）
                ->rules([
                    function ($attribute, $value, $fail) use ($form) {
                        $team = $form->getModelInstance(); // Team レコード
                        if ($value === null) return; // 解除はOK
                        $valid = DB::table('players')
                            ->join('player_team', 'player_team.player_id', '=', 'players.id')
                            ->where('player_team.team_id', $team->id)
                            ->where('players.user_id', $value)
                            ->exists();
                        if (! $valid) {
                            $fail('選択したユーザーはこのチームのメンバーではありません。');
                        }
                    }
                ]),
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
