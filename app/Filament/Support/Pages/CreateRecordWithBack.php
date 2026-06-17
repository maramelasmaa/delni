<?php

namespace App\Filament\Support\Pages;

use App\Filament\Support\Pages\Concerns\HasBackHeaderAction;
use Filament\Resources\Pages\CreateRecord;

abstract class CreateRecordWithBack extends CreateRecord
{
    use HasBackHeaderAction;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            ...parent::getHeaderActions(),
        ];
    }
}
