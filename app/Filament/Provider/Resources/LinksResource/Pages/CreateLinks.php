<?php

namespace App\Filament\Provider\Resources\LinksResource\Pages;

use App\Filament\Provider\Resources\LinksResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLinks extends CreateRecord
{
    protected static string $resource = LinksResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['profile_id'] = auth()->user()->profile->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
