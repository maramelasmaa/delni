<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use App\Models\OnboardingToken;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            ->modalDescription('أنشئ رابطاً جديداً لإعداد كلمة المرور. سيتم إلغاء الروابط السابقة.')
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
