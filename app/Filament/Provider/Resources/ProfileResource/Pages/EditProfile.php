<?php

namespace App\Filament\Provider\Resources\ProfileResource\Pages;

use App\Filament\Provider\Resources\ProfileResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use App\Services\ProfileCompletenessService;
use Filament\Actions\Action;

class EditProfile extends EditRecordWithBack
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return array_values(array_filter(
            parent::getHeaderActions(),
            fn (Action $action): bool => $action->getName() !== 'back',
        ));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->slug], panel: 'provider');
    }

    protected function afterSave(): void
    {
        app(ProfileCompletenessService::class)->evaluate($this->record->refresh());
    }
}
