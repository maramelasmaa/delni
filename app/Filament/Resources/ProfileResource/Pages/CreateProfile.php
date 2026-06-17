<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use App\Filament\Support\Pages\CreateRecordWithBack;

class CreateProfile extends CreateRecordWithBack
{
    protected static string $resource = ProfileResource::class;
}
