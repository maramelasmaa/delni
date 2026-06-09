<?php

namespace App\Filament\Provider\Resources\PortfolioResource\Pages;

use App\Filament\Provider\Resources\PortfolioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioItems extends ListRecords
{
    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
