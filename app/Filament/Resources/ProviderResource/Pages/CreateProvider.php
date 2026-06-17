<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Filament\Support\Pages\CreateRecordWithBack;
use App\Models\OnboardingToken;
use App\Models\User;
use App\Services\ProviderCreationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

class CreateProvider extends CreateRecordWithBack
{
    protected static string $resource = ProviderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): User {
            $accountData = ProviderResource::accountData($data);

            $accountData['password'] = bcrypt(Str::random(32));

            $record = User::query()->create($accountData);
            $record->assignRole(Role::findOrCreate('provider', 'web'));

            // CRITICAL: Create provider profile synchronously, inside transaction.
            // This ensures profile creation never depends on queue workers.
            $service = app(ProviderCreationService::class);
            $service->createProfileForUser($record);

            ProviderResource::saveProviderData($record, $data);

            OnboardingToken::query()->create([
                'user_id' => $record->id,
                'token' => Str::random(60),
                'expires_at' => now()->addHours(72),
            ]);

            return $record;
        });
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    protected function afterCreate(): void
    {
        try {
            $this->showOnboardingSuccessNotification();
        } catch (Throwable $e) {
            Log::error('Provider was created but setup link notification failed', [
                'provider_id' => $this->record?->getKey(),
                'email' => $this->record?->email,
                'exception' => $e->getMessage(),
            ]);

            Notification::make()
                ->title(__('filament.notifications.provider_created'))
                ->body(__('filament.notifications.provider_created_setup_link_hidden'))
                ->warning()
                ->persistent()
                ->send();
        }
    }

    protected function showOnboardingSuccessNotification(): void
    {
        $providerEmail = e((string) $this->record->email);
        $setupUrl = $this->latestSetupUrl();

        $content = __('filament.notifications.setup_link_send_manual', [
            'email' => $providerEmail,
            'link' => $setupUrl,
        ]);

        Notification::make()
            ->title(__('filament.notifications.provider_setup_link_generated'))
            ->body($content)
            ->actions([
                Action::make('copySetupLink')
                    ->label(__('filament.actions.copy_setup_link'))
                    ->icon('heroicon-o-clipboard')
                    ->color('gray')
                    ->actionJs('navigator.clipboard.writeText('.Js::from($setupUrl).')'),
                Action::make('openSetupLink')
                    ->label(__('filament.actions.open_setup_link'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url($setupUrl, shouldOpenInNewTab: true),
            ])
            ->success()
            ->persistent()
            ->send();
    }

    private function latestSetupUrl(): string
    {
        $token = $this->record
            ->onboardingTokens()
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if (! $token instanceof OnboardingToken) {
            return __('filament.notifications.setup_link_missing');
        }

        return route('onboarding.show', ['token' => $token->token]);
    }
}
