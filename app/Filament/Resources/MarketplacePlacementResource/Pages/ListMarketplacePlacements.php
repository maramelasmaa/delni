<?php

namespace App\Filament\Resources\MarketplacePlacementResource\Pages;

use App\Filament\Resources\MarketplacePlacementResource;
use Filament\Resources\Pages\ListRecords;

class ListMarketplacePlacements extends ListRecords
{
    protected static string $resource = MarketplacePlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
