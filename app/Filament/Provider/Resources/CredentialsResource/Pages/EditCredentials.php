<?php

namespace App\Filament\Provider\Resources\CredentialsResource\Pages;

use App\Filament\Provider\Resources\CredentialsResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use Filament\Actions;

class EditCredentials extends EditRecordWithBack
{
    protected static string $resource = CredentialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
