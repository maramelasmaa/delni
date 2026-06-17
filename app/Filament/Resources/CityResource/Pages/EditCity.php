<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use Filament\Actions;

class EditCity extends EditRecordWithBack
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            Actions\DeleteAction::make(),
        ];
    }
}
