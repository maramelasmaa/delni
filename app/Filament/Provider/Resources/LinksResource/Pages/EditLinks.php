<?php

namespace App\Filament\Provider\Resources\LinksResource\Pages;

use App\Filament\Provider\Resources\LinksResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLinks extends EditRecord
{
    protected static string $resource = LinksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
