<?php

namespace App\Filament\Provider\Resources\ProfileResource\Pages;

use App\Filament\Provider\Resources\ProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListProfiles extends ListRecords
{
    protected static string $resource = ProfileResource::class;
}
