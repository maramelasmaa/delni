<?php

namespace App\Filament\Support\Pages;

use App\Filament\Support\Pages\Concerns\HasBackHeaderAction;
use Filament\Resources\Pages\EditRecord;

abstract class EditRecordWithBack extends EditRecord
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
