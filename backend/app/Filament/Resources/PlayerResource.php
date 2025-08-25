<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerResource\Pages;
use App\Models\Player;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB ;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';

public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Section::make('選手情報')->schema([
            Forms\Components\TextInput::make('name')
                ->label('名前')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('kana')
                ->label('フリガナ')
                ->required()
                ->maxLength(100),

            Forms\Components\DatePicker::make('date_of_birth')
                ->label('生年月日')
                ->required()
                ->native(true),

            Forms\Components\TextInput::make('address')
                ->label('住所')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('phone')
                ->label('電話番号')
                ->tel()
                ->required()                         // ★ 必須に変更
                ->maxLength(30)
                ->unique(ignoreRecord: true),         // ★ 既存レコードは無視してユニーク

            Forms\Components\Select::make('initial_team_ids')
                ->label('所属チームを選択')
                ->multiple()
                ->preload()
                ->searchable()
                ->options(\App\Models\Team::query()->pluck('name', 'id'))
                ->helperText('選択すると即時所属となります'),
        ])->columns(2),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(function (EloquentBuilder $query) {   // ← 型をEloquentBuilderに
            $query->leftJoin('users', 'players.user_id', '=', 'users.id')
                ->select([
                    'players.*',
                    DB::raw('COALESCE(users.name, players.name) AS display_name'),
                    DB::raw('COALESCE(users.kana, players.kana) AS display_kana'),
                ])
                ->with(['teams']);
        })
        ->columns([
            Tables\Columns\TextColumn::make('display_name')->label('名前')
                ->sortable(query: fn (EloquentBuilder $q, string $d)  // ← ここも
                    => $q->orderBy(DB::raw('COALESCE(users.name, players.name)'), $d))
                ->searchable(query: function (EloquentBuilder $query, string $search) { // ← ここも
                    $like = "%{$search}%";
                    $query->where(fn ($q) =>
                        $q->where('players.name', 'like', $like)
                          ->orWhere('users.name', 'like', $like)
                    );
                }),

            Tables\Columns\TextColumn::make('display_kana')->label('フリガナ')
                ->sortable(query: fn (EloquentBuilder $q, string $d)
                    => $q->orderBy(DB::raw('COALESCE(users.kana, players.kana)'), $d))
                ->searchable(query: function (EloquentBuilder $query, string $search) {
                    $like = "%{$search}%";
                    $query->where(fn ($q) =>
                        $q->where('players.kana', 'like', $like)
                          ->orWhere('users.kana', 'like', $like)
                    );
                }),
            Tables\Columns\TextColumn::make('address')->label('住所')->limit(30),
            Tables\Columns\TextColumn::make('date_of_birth')->label('生年月日')->date('Y/m/d'),
            Tables\Columns\TextColumn::make('teams.name')->label('所属チーム')->badge()->separator('、'),
            // ★ 代表者フラグ（○/×）
            Tables\Columns\IconColumn::make('is_leader')
                ->label('代表者')
                ->state(function (\App\Models\Player $record) {
                    // user_id があり、所属チームのどれかの leader_id と一致したら代表者
                    if (!$record->user_id) return false;
                    return $record->teams->contains(fn ($t) => (int)$t->leader_id === (int)$record->user_id);
                })
                ->boolean(), // true=チェック、false=×（デフォルトのアイコンでOK）
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('teams')
                ->label('所属チーム')
                ->relationship('teams', 'name'),

            Tables\Filters\TernaryFilter::make('is_leader')
                ->label('代表者')
                ->placeholder('すべて')
                ->trueLabel('代表のみ')
                ->falseLabel('代表以外')
                ->queries(
                    true: function (EloquentBuilder $query) {
                        $query->whereExists(function (QueryBuilder $q) {
                            $q->from('player_team')
                              ->join('teams', 'teams.id', '=', 'player_team.team_id')
                              ->whereColumn('player_team.player_id', 'players.id')
                              ->whereColumn('teams.leader_id', 'players.user_id');
                        });
                    },
                    false: function (EloquentBuilder $query) {
                        $query->whereNotExists(function (QueryBuilder $q) {
                            $q->from('player_team')
                              ->join('teams', 'teams.id', '=', 'player_team.team_id')
                              ->whereColumn('player_team.player_id', 'players.id')
                              ->whereColumn('teams.leader_id', 'players.user_id');
                        });
                    },
                    blank: fn (EloquentBuilder $query) => $query,
                ),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPlayers::route('/'),
            'create' => Pages\CreatePlayer::route('/create'),
            'edit'   => Pages\EditPlayer::route('/{record}/edit'),
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
