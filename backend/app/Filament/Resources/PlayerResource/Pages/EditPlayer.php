<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use Filament\Resources\Pages\EditRecord;

class EditPlayer extends EditRecord
{
    protected static string $resource = PlayerResource::class;

    // 既存の所属をフォームに初期表示
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Player $player */
        $player = $this->record;
        $data['initial_team_ids'] = $player->teams()->pluck('teams.id')->all();
        return $data;
    }

    // 保存後：フォームの選択結果で pivot を「置き換え」る
    protected function afterSave(): void
    {
        /** @var \App\Models\Player $player */
        $player = $this->record;

        $selected = collect($this->form->getState()['initial_team_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        // 何も選んでいない＝全部外す運用ならそのまま sync([])。
        // 「未選択は現状維持」にしたいなら return; に変えてね。
        $pivot = [];

        // 既存の所属を見て、残すものは joined_at を維持、新規は now() を入れる
        $current = $player->teams()->pluck('player_team.joined_at', 'teams.id')->all();

        foreach ($selected as $teamId) {
            $pivot[$teamId] = [
                'status'      => 'approved',
                'joined_at'   => $current[$teamId] ?? now(),
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ];
        }

        $player->teams()->sync($pivot);
    }
}