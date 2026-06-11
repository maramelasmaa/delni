<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\SetPasswordMail;
use App\Models\OnboardingToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OnboardingLinkService
{
    public function resend(User $user): void
    {
        if (! $user->hasRole('provider')) {
            throw new \InvalidArgumentException('User is not a provider');
        }

        // Create new token or extend existing unused one
        $onboardingToken = OnboardingToken::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->first();

        if ($onboardingToken) {
            // Extend existing unused token
            $onboardingToken->update(['expires_at' => now()->addHours(72)]);
        } else {
            // Create new token
            $onboardingToken = OnboardingToken::create([
                'user_id' => $user->id,
                'token' => Str::random(60),
                'expires_at' => now()->addHours(72),
            ]);
        }

        // Resend actions should deliver immediately; Mail::send() queues ShouldQueue mailables.
        $setPasswordLink = route('onboarding.show', ['token' => $onboardingToken->token]);
        Log::info('Resending provider onboarding email', [
            'provider_id' => $user->id,
            'email' => $user->email,
            'mail_mailer' => config('mail.default'),
            'selected_mailer' => 'resend',
            'queue_connection' => config('queue.default'),
        ]);

        Mail::mailer('resend')->sendNow(new SetPasswordMail(
            email: $user->email,
            setPasswordLink: $setPasswordLink,
            userName: $user->name,
        ));

        Log::info('Provider onboarding email resend completed', [
            'provider_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function canResend(User $user): bool
    {
        return $user->hasRole('provider');
    }
}
