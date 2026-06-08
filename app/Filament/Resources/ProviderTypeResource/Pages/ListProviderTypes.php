<?php

namespace App\Filament\Resources\ProviderTypeResource\Pages;

use App\Filament\Resources\ProviderTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProviderTypes extends ListRecords
{
    protected static string $resource = ProviderTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
