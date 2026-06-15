<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\OnboardingLinkService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-onboarding-link {email : The provider\'s email address}')]
#[Description('Generate an onboarding setup link for a provider')]
class GenerateOnboardingLink extends Command
{
    public function handle(OnboardingLinkService $service): int
    {
        $email = (string) $this->argument('email');
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("User not found: {$email}");

            return static::FAILURE;
        }

        try {
            $setupLink = $service->createOrRefreshLink($user);
            $this->info("Onboarding setup link for {$email}:");
            $this->line($setupLink);

            return static::SUCCESS;
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return static::FAILURE;
        }
    }
}
