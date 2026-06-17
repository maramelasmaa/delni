<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use Filament\Actions;

class EditReview extends EditRecordWithBack
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            Actions\DeleteAction::make()
                ->visible(fn ($record): bool => ! $record->trashed()),
            Actions\RestoreAction::make()
                ->visible(fn ($record): bool => $record->trashed()),
        ];
    }
}
