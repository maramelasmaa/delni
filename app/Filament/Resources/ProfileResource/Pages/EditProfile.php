<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use Filament\Actions;

class EditProfile extends EditRecordWithBack
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            Actions\DeleteAction::make(),
        ];
    }
}
