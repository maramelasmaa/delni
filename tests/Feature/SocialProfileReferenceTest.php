<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Rules\SocialProfileReference;
use PHPUnit\Framework\TestCase;

class SocialProfileReferenceTest extends TestCase
{
    /**
     * @return string[]
     */
    private function validate(string $platform, mixed $value): array
    {
        $errors = [];

        (new SocialProfileReference($platform))->validate(
            'social',
            $value,
            function (string $message) use (&$errors): void {
                $errors[] = $message;
            }
        );

        return $errors;
    }

    public function test_instagram_accepts_handle_or_platform_url(): void
    {
        $this->assertEmpty($this->validate('instagram', 'example.user'));
        $this->assertEmpty($this->validate('instagram', 'https://instagram.com/example.user'));
    }

    public function test_instagram_rejects_wrong_host(): void
    {
        $this->assertNotEmpty($this->validate('instagram', 'https://evilinstagram.com/example.user'));
    }

    public function test_linkedin_rejects_non_linkedin_url(): void
    {
        $this->assertNotEmpty($this->validate('linkedin', 'https://example.com/in/example-user'));
    }

    public function test_github_rejects_trailing_hyphen_usernames(): void
    {
        $this->assertNotEmpty($this->validate('github', 'bad-user-'));
    }
}
