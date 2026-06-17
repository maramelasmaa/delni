<?php

namespace App\Filament\Resources\ContactInfos\Pages;

use App\Filament\Resources\ContactInfos\ContactInfoResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use Filament\Actions\DeleteAction;

class EditContactInfo extends EditRecordWithBack
{
    protected static string $resource = ContactInfoResource::class;

    public function getHeading(): string
    {
        return __('filament.page_headings.edit_contact_info');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            DeleteAction::make(),
        ];
    }
}
