<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validate that ends_at is after starts_at (redundant with form validation, but safe)
        if (isset($data['starts_at']) && isset($data['ends_at'])) {
            if ($data['ends_at'] <= $data['starts_at']) {
                Notification::make()
                    ->title('Invalid Dates')
                    ->body('End date must be after start date.')
                    ->danger()
                    ->send();
                $this->halt();
            }
        }

        // Subscriptions are active immediately (observer sets is_active=true, approved_at=now()).
        // No separate approval step — admin creation = payment confirmed = subscription active.
        return $data;
    }
}
