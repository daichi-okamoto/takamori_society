<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlayer extends CreateRecord
{
    protected static string $resource = PlayerResource::class;

    protected function afterCreate(): void
    {
        /** @var \App\Models\Player $player */
        $player = $this->record;

        $teamIds = collect($this->form->getState()['initial_team_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($teamIds === []) return;

        $pivot = [];
        foreach ($teamIds as $teamId) {
            $pivot[$teamId] = [
                'status'      => 'approved',
                'joined_at'   => now(),
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ];
        }

        $player->teams()->syncWithoutDetaching($pivot);
    }
}
