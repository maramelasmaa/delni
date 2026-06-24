<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use App\Services\OnboardingLinkService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditProvider extends EditRecordWithBack
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            $this->getGenerateSetPasswordLinkAction(),
            Actions\DeleteAction::make(),
        ];
    }

    private function getGenerateSetPasswordLinkAction(): Actions\Action
    {
        return Actions\Action::make('generate_set_password_link')
            ->label(__('filament.actions.generate_setup_link'))
            ->icon('heroicon-o-link')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading(__('filament.actions.generate_setup_link'))
            ->modalDescription(__('filament.help_text.setup_link_regenerate_confirmation'))
            ->action(function (OnboardingLinkService $service): void {
                DB::transaction(function () use ($service): void {
                    $user = $this->record;
                    $setPasswordLink = $service->createOrRefreshLink($user);

                    Notification::make()
                        ->title(__('filament.notifications.setup_link_generated'))
                        ->body(__('filament.notifications.setup_link_copy_send', ['email' => $user->email, 'link' => $setPasswordLink]))
                        ->success()
                        ->persistent()
                        ->send();
                });
            });
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

            ProviderResource::saveProviderData($record, $data);

            return $record;
        });
    }
}
