<?php

namespace App\Filament\Provider\Resources\LinksResource\Pages;

use App\Filament\Provider\Resources\LinksResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLinks extends ListRecords
{
    protected static string $resource = LinksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
