<?php

namespace App\Filament\Resources\ContactInfos\Pages;

use App\Filament\Resources\ContactInfos\ContactInfoResource;
use Filament\Resources\Pages\EditRecord;

class EditContactInfo extends EditRecord
{
    protected static string $resource = ContactInfoResource::class;

    public function getHeading(): string
    {
        return __('filament.page_headings.edit_contact_info');
    }
}
