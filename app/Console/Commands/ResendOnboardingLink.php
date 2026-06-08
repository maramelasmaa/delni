<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\OnboardingLinkService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:resend-onboarding-link {email : The provider\'s email address}')]
#[Description('Resend onboarding setup link to a provider')]
class ResendOnboardingLink extends Command
{
    public function handle(OnboardingLinkService $service): int
    {
        $email = $this->argument('email');
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("User not found: {$email}");

            return static::FAILURE;
        }

        try {
            $service->resend($user);
            $this->info("Onboarding link resent to: {$email}");

            return static::SUCCESS;
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return static::FAILURE;
        }
    }
}
