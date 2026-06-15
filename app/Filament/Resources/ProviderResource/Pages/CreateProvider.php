<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Models\OnboardingToken;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateProvider extends CreateRecord
{
    protected static string $resource = ProviderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): User {
            $accountData = ProviderResource::accountData($data);

            $accountData['password'] = bcrypt(Str::random(32));

            $record = User::query()->create($accountData);
            $record->assignRole('provider');

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
        $this->showOnboardingSuccessNotification();
    }

    protected function showOnboardingSuccessNotification(): void
    {
        $providerEmail = e((string) $this->record->email);
        $setupUrl = $this->latestSetupUrl();

        $content = <<<HTML
            <div style="text-align: left; padding: 8px 0;">
                <p style="margin: 0 0 12px; color: #059669; font-size: 15px; font-weight: 700;">
                    Provider created successfully.
                </p>
                <p style="margin: 0 0 14px; color: #374151; font-size: 14px;">
                    Copy this secure setup link and send it to <strong>{$providerEmail}</strong>. Delni does not send onboarding emails automatically.
                </p>
                <p style="word-break: break-all; margin: 0; padding: 12px; border-radius: 8px; background: #f3f4f6; color: #111827; font-size: 13px;">
                    {$setupUrl}
                </p>
                <p style="margin: 12px 0 0; color: #92400e; font-size: 12px;">
                    The setup link expires in 72 hours.
                </p>
            </div>
        HTML;

        Notification::make()
            ->title('Provider setup link generated')
            ->body($content)
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
            return 'No active setup link was generated.';
        }

        return route('onboarding.show', ['token' => $token->token]);
    }
}
