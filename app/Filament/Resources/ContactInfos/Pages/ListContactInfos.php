<?php

namespace App\Filament\Resources\ContactInfos\Pages;

use App\Filament\Resources\ContactInfos\ContactInfoResource;
use App\Models\ContactInfo;
use Filament\Resources\Pages\ListRecords;

class ListContactInfos extends ListRecords
{
    protected static string $resource = ContactInfoResource::class;

    public function mount(): void
    {
        redirect(ContactInfoResource::getUrl('edit', ['record' => ContactInfo::instance()]))->send();
    }
}
