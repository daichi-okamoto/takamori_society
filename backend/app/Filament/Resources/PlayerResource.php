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

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ▼ User モデルへ保存（relationship('user')）
            Forms\Components\Group::make([
                Forms\Components\TextInput::make('name')
                    ->label('名前')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('kana')
                    ->label('フリガナ')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('メールアドレス')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                // ← 表示しない（Hidden）でパスワード自動生成・ハッシュ保存
                Forms\Components\Hidden::make('password')
                    ->dehydrateStateUsing(fn () => Hash::make(Str::random(24)))
                    ->dehydrated(true),

                // 役割も Hidden で自動
                Forms\Components\Hidden::make('role')
                    ->default('player')
                    ->dehydrated(true),

                // ※メール確認済みにしたいなら（不要なら消してください）
                Forms\Components\Hidden::make('email_verified_at')
                    ->default(fn () => now())
                    ->dehydrated(true),
            ])->relationship('user'),

            // ▼ Player 側
            Forms\Components\TextInput::make('address')
                ->label('住所')
                ->required()
                ->maxLength(255),

            Forms\Components\DatePicker::make('date_of_birth')
                ->label('生年月日')
                ->required(),

            Forms\Components\Select::make('teams')
                ->label('所属チーム')
                ->multiple()
                ->relationship('teams', 'name')
                ->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('名前')->searchable(),
                Tables\Columns\TextColumn::make('user.kana')->label('フリガナ')->searchable(),
                Tables\Columns\TextColumn::make('address')->label('住所')->limit(30),
                Tables\Columns\TextColumn::make('date_of_birth')->label('生年月日')->date(),
                Tables\Columns\TextColumn::make('teams.name')->label('所属チーム')->badge()->separator('、'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teams')
                    ->label('所属チーム')
                    ->relationship('teams', 'name')
                    ->multiple(false),
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
