<?php

namespace App\Filament\Resources\MarketplacePlacementResource\Pages;

use App\Filament\Resources\MarketplacePlacementResource;
use App\Filament\Support\Pages\EditRecordWithBack;

class EditMarketplacePlacement extends EditRecordWithBack
{
    protected static string $resource = MarketplacePlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
        ];
    }
}
