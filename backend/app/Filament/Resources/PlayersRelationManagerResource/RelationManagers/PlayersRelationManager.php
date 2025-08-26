<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use App\Models\Team;
use App\Models\Player;

class PlayersRelationManager extends RelationManager
{
    protected static string $relationship = 'players';
    protected static ?string $title = 'チームメンバー';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('名前')->required()->maxLength(255),
            Forms\Components\TextInput::make('kana')->label('フリガナ')->required()->maxLength(255),
            Forms\Components\TextInput::make('phone')->label('電話番号')->tel()->required()
                ->rule('regex:/^[0-9\-]+$/')->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('address')->label('住所')->required()->maxLength(255),
            Forms\Components\DatePicker::make('date_of_birth')->label('生年月日')->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('名前')
                    ->state(fn (Player $r) => $r->user?->name ?? $r->name)->searchable(),
                Tables\Columns\TextColumn::make('kana')->label('フリガナ')
                    ->state(fn (Player $r) => $r->user?->kana ?? $r->kana)->searchable(),
                Tables\Columns\TextColumn::make('user.email')->label('メールアドレス')->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')->label('電話番号'),
                Tables\Columns\IconColumn::make('is_leader')->label('代表者')
                    ->state(function (Player $record) {
                        if (! $record->user_id) return false;
                        return $record->teams->contains(fn ($t) => (int)$t->leader_id === (int)$record->user_id);
                    })
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('既存選手を追加')
                    ->modalHeading('既存選手を追加')
                    ->modalSubmitActionLabel('チームに追加')
                    ->modalCancelActionLabel('キャンセル')
                    ->attachAnother(false)
                    ->form(function (AttachAction $action) {
                        /** @var \App\Models\Team|null $owner */
                        $owner  = $this->getOwnerRecord();
                        $teamId = $owner?->id;

                        $recordSelect = $action->getRecordSelect()
                            ->label('選手')
                            ->placeholder('選手を検索…')
                            ->multiple()
                            ->searchable()
                            ->preload() // ★ 未入力でも最初の候補を表示
                            ->getSearchResultsUsing(function (string $search) use ($teamId) {
                                $q = \App\Models\Player::query()
                                    // このチームに既にいる選手は除外（他チーム所属はOK）
                                    ->when($teamId, function ($qq) use ($teamId) {
                                        $qq->whereNotIn('players.id', function ($sq) use ($teamId) {
                                            $sq->from('player_team')->where('team_id', $teamId)->select('player_id');
                                        });
                                    })
                                    // 検索語があれば name / kana / phone をLIKE
                                    ->when($search !== '', function ($qq) use ($search) {
                                        $like = "%{$search}%";
                                        $qq->where(function ($w) use ($like) {
                                            $w->where('players.name', 'like', $like)
                                            ->orWhere('players.kana', 'like', $like)
                                            ->orWhere('players.phone', 'like', $like);
                                        });
                                    })
                                    ->orderBy('players.name')
                                    ->limit(50);

                                return $q->pluck('name', 'id')->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value) => \App\Models\Player::find($value)?->name ?? '');

                        return [
                            $recordSelect,
                            // pivot 初期値
                            \Filament\Forms\Components\Hidden::make('status')->default('approved'),
                            \Filament\Forms\Components\Hidden::make('joined_at')->default(fn () => now()),
                            \Filament\Forms\Components\Hidden::make('approved_at')->default(fn () => now()),
                            \Filament\Forms\Components\Hidden::make('approved_by')->default(fn () => auth()->id()),
                        ];
                    }),

                \Filament\Tables\Actions\CreateAction::make()->label('新しい選手を作成'),
            ])
            ->actions([
                // 代表者に設定
                Action::make('makeLeader')
                    ->label('代表者に設定')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Player $r) => (bool) $r->user_id)
                    ->action(function (Player $record): void {
                        /** @var Team $owner */
                        $owner = $this->getOwnerRecord();
                        if (! $owner?->id) {
                            Notification::make()->danger()->title('チームが取得できませんでした')->send();
                            return;
                        }
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

                // 代表者解除
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

                        Notification::make()->success()->title('代表者を解除しました')->send();
                    }),
                     // メンバーから外す（任意）
                DetachAction::make()->label('退団')->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
