<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use Filament\Resources\Pages\EditRecord;

class EditPlayer extends EditRecord
{
    protected static string $resource = PlayerResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user']['name'] = $this->record->user?->name;
        $data['user']['kana'] = $this->record->user?->kana;
        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();

        $this->record->user->update([
            'name' => $state['user']['name'] ?? $this->record->user->name,
            'kana' => $state['user']['kana'] ?? $this->record->user->kana,
        ]);
    }
}
