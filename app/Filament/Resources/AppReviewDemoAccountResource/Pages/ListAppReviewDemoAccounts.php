<?php

namespace App\Filament\Resources\AppReviewDemoAccountResource\Pages;

use App\Filament\Resources\AppReviewDemoAccountResource;
use Filament\Resources\Pages\ListRecords;

class ListAppReviewDemoAccounts extends ListRecords
{
    protected static string $resource = AppReviewDemoAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
