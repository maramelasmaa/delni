<?php

namespace App\Filament\Provider\Resources\ReviewsResource\Pages;

use App\Filament\Provider\Resources\ReviewsResource;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewsResource::class;

    protected static ?string $title = 'التقييمات';
}
