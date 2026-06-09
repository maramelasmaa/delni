<?php

namespace App\Filament\Provider\Resources\SubscriptionResource\Pages;

use App\Filament\Provider\Resources\SubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected static ?string $title = 'الاشتراكات';
}
