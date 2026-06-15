<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Models\OnboardingToken;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditProvider extends EditRecord
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGenerateSetPasswordLinkAction(),
            Actions\DeleteAction::make(),
        ];
    }

    private function getGenerateSetPasswordLinkAction(): Actions\Action
    {
        return Actions\Action::make('generate_set_password_link')
            ->label('Generate Setup Link')
            ->icon('heroicon-o-link')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Generate Setup Link')
            ->modalDescription('Create a new password setup link. Old setup links will be revoked.')
            ->action(function (): void {
                DB::transaction(function (): void {
                    $user = $this->record;

                    OnboardingToken::query()
                        ->where('user_id', $user->id)
                        ->delete();

                    $onboardingToken = OnboardingToken::query()->create([
                        'user_id' => $user->id,
                        'token' => Str::random(60),
                        'expires_at' => now()->addHours(72),
                    ]);

                    $setPasswordLink = route('onboarding.show', ['token' => $onboardingToken->token]);

                    Notification::make()
                        ->title('Setup link generated')
                        ->body("Copy and send this setup link to {$user->email}: {$setPasswordLink}")
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
