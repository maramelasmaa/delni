<?php

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Services\OnboardingLinkService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditProvider extends EditRecord
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resendOnboardingEmail')
                ->label('Resend Onboarding Email')
                ->icon('heroicon-m-envelope')
                ->action(function (OnboardingLinkService $service): void {
                    $service->resend($this->record);
                    Notification::make()
                        ->title('Email sent')
                        ->body('Onboarding email has been resent to '.$this->record->email)
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Resend Onboarding Email')
                ->modalDescription('This will send a new onboarding email to '.$this->record->email.'. They will receive a new password setup link.')
                ->modalSubmitActionLabel('Resend'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ProviderResource::fillProviderFormData($data, $this->record);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Model {
            $accountData = ProviderResource::accountData($data);

            if (array_key_exists('password', $accountData) && filled($accountData['password'])) {
                $accountData['password'] = Hash::make($accountData['password']);
            }

            $record->update($accountData);

            // Save all provider data (profile, subscription, marketplace)
            ProviderResource::saveProviderData($record, $data);

            return $record;
        });
    }
}
