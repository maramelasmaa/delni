<?php

namespace App\Filament\Provider\Resources\ProfileResource\Pages;

use App\Filament\Provider\Resources\ProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListProfiles extends ListRecords
{
    protected static string $resource = ProfileResource::class;

    public function mount(): void
    {
        $profile = auth()->user()?->profile;

        if ($profile) {
            redirect(ProfileResource::getUrl('edit', ['record' => $profile->slug], panel: 'provider'))->send();
        } else {
            redirect(ProfileResource::getUrl('create', panel: 'provider'))->send();
        }
    }
}
