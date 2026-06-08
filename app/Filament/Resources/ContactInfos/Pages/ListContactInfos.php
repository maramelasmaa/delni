<?php

namespace App\Filament\Resources\ContactInfos\Pages;

use App\Filament\Resources\ContactInfos\ContactInfoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactInfos extends ListRecords
{
    protected static string $resource = ContactInfoResource::class;

    public function getHeading(): string
    {
        return __('filament.models.contact_info_plural');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
