<?php

namespace App\Filament\Resources\ContactInfos\Pages;

use App\Filament\Resources\ContactInfos\ContactInfoResource;
use App\Filament\Support\Pages\CreateRecordWithBack;

class CreateContactInfo extends CreateRecordWithBack
{
    protected static string $resource = ContactInfoResource::class;

    public function getHeading(): string
    {
        return __('filament.page_headings.create_contact_info');
    }
}
