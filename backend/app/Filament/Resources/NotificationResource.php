<?php

namespace App\Filament\Resources;

use App\Models\Notification;
use App\Models\Team; 
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NotificationResource\Pages;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $navigationLabel = '通知管理';
    protected static ?string $navigationGroup = 'お知らせ管理';
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

     // 一覧画面のタイトル
    public static function getPluralLabel(): string
    {
        return '通知一覧';
    }
    // 単体レコード表示用（編集画面など）
    public static function getLabel(): string
    {
        return '通知';
    }

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('title')->required(),
            Textarea::make('message')->required(),

            Select::make('target_type')
                ->label('通知対象')
                ->options([
                    'team_players' => '特定チームの選手',
                    'team_leaders' => '特定チームの代表者',
                    'tournament_players' => '大会に参加している全選手',
                    'tournament_leaders' => '大会に参加しているチームの代表者',
                    'all_players' => '全選手',
                    'all_leaders' => '全代表者',
                    'all_users' => '全ユーザー',
                ])
                ->reactive()
                ->required(),

            Select::make('tournament_id')
                ->label('対象大会')
                ->options(Tournament::pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Forms\Get $get) => in_array($get('target_type'), ['tournament_players', 'tournament_leaders']))
                ->required(fn (Forms\Get $get) => in_array($get('target_type'), ['tournament_players', 'tournament_leaders'])),

            Select::make('team_id')
                ->label('対象チーム')
                ->options(Team::all()->pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Forms\Get $get) => in_array($get('target_type'), ['team_players', 'team_leaders']))
                ->required(fn (Forms\Get $get) => in_array($get('target_type'), ['team_players', 'team_leaders'])),

            DateTimePicker::make('sent_at')->label('通知日時'),
        ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('タイトル')->searchable()->sortable(),
                TextColumn::make('target_type')
                    ->label('通知先')
                    ->formatStateUsing(function ($state, Notification $record) {
                        return match ($state) {
                            'team_players' => optional($record->team)->name . 'の選手',
                            'team_leaders' => optional($record->team)->name . 'の代表者',
                            'tournament_players' => optional($record->tournament)->name . 'の参加選手',
                            'tournament_leaders' => optional($record->tournament)->name . '参加の代表者',
                            'all_players' => '全選手',
                            'all_leaders' => '全代表者',
                            'all_users' => '全ユーザー',
                            default => '未設定',
                        };
                    }),
                TextColumn::make('sent_at')->label('通知日時')->sortable(),
                TextColumn::make('message')->label('内容')->limit(50)->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('sent_at', 'desc')

            // ✅ 編集・削除ボタンを表示
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
