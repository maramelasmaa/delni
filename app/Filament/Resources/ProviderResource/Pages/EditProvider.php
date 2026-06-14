<?php

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Mail\PasswordResetMail;
use App\Mail\SetPasswordMail;
use App\Models\OnboardingToken;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class EditProvider extends EditRecord
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getResendSetPasswordAction(),
            $this->getSendPasswordResetAction(),
            Actions\DeleteAction::make(),
        ];
    }

    private function getResendSetPasswordAction(): Actions\Action
    {
        return Actions\Action::make('resend_set_password')
            ->label('Resend Set Password Email')
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Resend Set Password Email')
            ->modalDescription('Send a new password setup link to the provider. The previous link will still work.')
            ->action(function (): void {
                DB::transaction(function (): void {
                    $user = $this->record;

                    // Create new onboarding token
                    OnboardingToken::where('user_id', $user->id)->delete();

                    $onboardingToken = OnboardingToken::create([
                        'user_id' => $user->id,
                        'token' => Str::random(60),
                        'expires_at' => now()->addHours(72),
                    ]);

                    $setPasswordLink = route('onboarding.show', ['token' => $onboardingToken->token]);
                    Mail::queue(new SetPasswordMail(
                        email: $user->email,
                        setPasswordLink: $setPasswordLink,
                        userName: $user->name,
                    ));

                    Notification::make()
                        ->title('✓ Email Queued')
                        ->body("Set password email sent to {$user->email}")
                        ->success()
                        ->send();
                });
            });
    }

    private function getSendPasswordResetAction(): Actions\Action
    {
        return Actions\Action::make('send_password_reset')
            ->label('Send Password Reset Email')
            ->icon('heroicon-o-key')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Send Password Reset Email')
            ->modalDescription('Send a password reset link to the provider. They can use it to reset their password.')
            ->action(function (): void {
                $user = $this->record;

                $token = Password::createToken($user);

                $resetLink = route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ]);

                Mail::queue(new PasswordResetMail(
                    email: $user->email,
                    resetLink: $resetLink,
                    userName: $user->name,
                ));

                Notification::make()
                    ->title('✓ Email Queued')
                    ->body("Password reset email sent to {$user->email}")
                    ->success()
                    ->send();
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

            // Save all provider data (profile, subscription, marketplace)
            ProviderResource::saveProviderData($record, $data);

            return $record;
        });
    }
}
