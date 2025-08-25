<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use App\Models\Team;
use App\Models\Player;
use Filament\Notifications\Notification;

class PlayersRelationManager extends RelationManager
{
    protected static string $relationship = 'players';
    protected static ?string $title = 'チームメンバー';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名前')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('kana')
                    ->label('フリガナ')
                    ->required()
                    ->maxLength(255),

                // ★ 電話番号必須
                Forms\Components\TextInput::make('phone')
                    ->label('電話番号')
                    ->tel()
                    ->required() // ← 新規作成・編集どちらも必須にしたいならこれ
                    ->rule('regex:/^[0-9\-]+$/')
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('address')
                    ->label('住所')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('date_of_birth')
                    ->label('生年月日')
                    ->required(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                // 表示は user が無い選手も考慮してフォールバックさせる
                Tables\Columns\TextColumn::make('name')
                    ->label('名前')
                    ->state(fn (Player $r) => $r->user?->name ?? $r->name)
                    ->searchable(),

                Tables\Columns\TextColumn::make('kana')
                    ->label('フリガナ')
                    ->state(fn (Player $r) => $r->user?->kana ?? $r->kana)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('メールアドレス')
                    ->placeholder('—'),

                // 今のチームの代表かどうかを一目で
                Tables\Columns\IconColumn::make('is_leader')
                    ->label('代表者')
                    ->state(function (\App\Models\Player $record) {
                        // user_id があり、所属チームのどれかの leader_id と一致したら代表者
                        if (!$record->user_id) return false;
                        return $record->teams->contains(fn ($t) => (int)$t->leader_id === (int)$record->user_id);
                    })
                    ->boolean(), // true=チェック、false=×（デフォルトのアイコンでOK）
                ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('メンバー追加'),
            ])
            ->actions([
                // 代表者に設定
                Action::make('makeLeader')
                ->label('代表者に設定')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (Player $r) => (bool) $r->user_id)
                ->action(function (Player $record): void {
                    /** @var Team $owner */
                    $owner = $this->getOwnerRecord();   // ← 親チームを取得（これが超重要）

                    if (! $owner?->id) {
                        Notification::make()->danger()->title('チームが取得できませんでした')->send();
                        return;
                    }

                    // 念のため所属を保証（無ければ付与）
                    if (! $owner->players()->whereKey($record->id)->exists()) {
                        $owner->players()->attach($record->id, [
                            'status'      => 'approved',
                            'joined_at'   => now(),
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                    }

                    $owner->leader_id = $record->user_id;
                    $owner->save();

                    Notification::make()->success()->title("{$record->name} を代表者に設定しました")->send();
                }),

            Action::make('unsetLeader')
                ->label('代表者解除')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn (Player $r) => (function () use ($r) {
                    $owner = $this->getOwnerRecord();
                    return $r->user_id && $owner && (int)$owner->leader_id === (int)$r->user_id;
                })())
                ->requiresConfirmation()
                ->action(function (Player $record): void {
                    /** @var Team $owner */
                    $owner = $this->getOwnerRecord();

                    if (! $owner?->id) {
                        Notification::make()->danger()->title('チームが取得できませんでした')->send();
                        return;
                    }

                    $owner->leader_id = null;
                    $owner->save();

                    Notification::make()->success()->title("代表者を解除しました")->send();
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
