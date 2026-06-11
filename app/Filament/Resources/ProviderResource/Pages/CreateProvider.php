<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Mail\SetPasswordMail;
use App\Models\OnboardingToken;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateProvider extends CreateRecord
{
    protected static string $resource = ProviderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): User {
            $accountData = ProviderResource::accountData($data);

            // Create account with a placeholder password (provider must set via onboarding email)
            $accountData['password'] = bcrypt(Str::random(32));

            $record = User::query()->create($accountData);

            // Always assign provider role
            $record->assignRole('provider');

            // Save all provider data (profile, subscription, marketplace)
            ProviderResource::saveProviderData($record, $data);

            // Create onboarding token (valid for 72 hours to account for delayed/spam emails)
            $onboardingToken = OnboardingToken::create([
                'user_id' => $record->id,
                'token' => Str::random(60),
                'expires_at' => now()->addHours(72),
            ]);

            // Queue onboarding email (SetPasswordMail implements ShouldQueue)
            $setPasswordLink = route('onboarding.show', ['token' => $onboardingToken->token]);
            Mail::send(new SetPasswordMail(
                email: $record->email,
                setPasswordLink: $setPasswordLink,
                userName: $record->name,
            ));

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
        $providerName = $this->record->name;
        $providerEmail = $this->record->email;

        $content = <<<HTML
            <div style="text-align: center; padding: 20px;">
                <p style="margin-bottom: 20px; color: #059669; font-size: 16px; font-weight: 600;">
                    ✓ Provider created successfully!
                </p>

                <div style="background: #e7f3ff; border: 2px solid #b3d9ff; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <p style="margin: 0 0 10px 0; font-size: 12px; color: #0c5394; text-transform: uppercase; font-weight: 600;">✉️ Onboarding Email Queued</p>
                    <p style="margin: 0; font-size: 14px; color: #0c5394;">
                        An onboarding email with a secure password setup link has been sent to <strong>$providerEmail</strong>
                    </p>
                </div>

                <div style="background: #f3f4f6; border-radius: 6px; padding: 15px; margin: 20px 0; text-align: left; font-size: 13px; color: #666;">
                    <p style="margin: 0 0 8px 0;"><strong>What Happens Next:</strong></p>
                    <p style="margin: 5px 0;">1️⃣ Provider receives onboarding email</p>
                    <p style="margin: 5px 0;">2️⃣ Provider clicks secure setup link</p>
                    <p style="margin: 5px 0;">3️⃣ Provider sets their own password</p>
                    <p style="margin: 5px 0;">4️⃣ Provider can login to dashboard</p>
                </div>

                <div style="background: #fff5e6; border: 1px solid #ffe8b6; border-radius: 5px; padding: 12px; margin: 15px 0; font-size: 12px; color: #664d00;">
                    <strong>Note:</strong> The setup link expires in 24 hours. If needed, you can resend the onboarding email from the provider details page.
                </div>
            </div>
        HTML;

        Notification::make()
            ->title('Provider Onboarding Started')
            ->body($content)
            ->success()
            ->persistent()
            ->send();
    }
}
