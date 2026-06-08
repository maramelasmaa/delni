<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validate that ends_at is after starts_at
        if (isset($data['ends_at']) && $this->record->starts_at) {
            if ($data['ends_at'] <= $this->record->starts_at) {
                Notification::make()
                    ->title('Invalid Dates')
                    ->body('End date must be after start date.')
                    ->danger()
                    ->send();
                $this->halt();
            }
        }

        return $data;
    }
}
