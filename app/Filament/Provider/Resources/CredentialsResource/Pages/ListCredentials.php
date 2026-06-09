<?php

namespace App\Filament\Provider\Resources\CredentialsResource\Pages;

use App\Filament\Provider\Resources\CredentialsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCredentials extends ListRecords
{
    protected static string $resource = CredentialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
