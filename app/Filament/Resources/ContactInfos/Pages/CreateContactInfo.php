<?php

namespace App\Filament\Resources\ContactInfos\Pages;

use App\Filament\Resources\ContactInfos\ContactInfoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContactInfo extends CreateRecord
{
    protected static string $resource = ContactInfoResource::class;

    public function getHeading(): string
    {
        return __('filament.page_headings.create_contact_info');
    }
}
