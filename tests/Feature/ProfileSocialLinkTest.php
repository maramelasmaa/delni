<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Profile;
use PHPUnit\Framework\TestCase;

class ProfileSocialLinkTest extends TestCase
{
    public function test_profile_generates_canonical_social_urls_from_handle_columns(): void
    {
        $profile = new Profile([
            'instagram_handle' => 'insta.user',
            'facebook_slug' => 'brand.page',
            'linkedin_slug' => 'in/professional-name',
            'github_username' => 'octocat',
        ]);

        $this->assertSame('https://instagram.com/insta.user', $profile->instagram);
        $this->assertSame('https://facebook.com/brand.page', $profile->facebook);
        $this->assertSame('https://linkedin.com/in/professional-name', $profile->linkedin);
        $this->assertSame('https://github.com/octocat', $profile->github);
    }

    public function test_profile_normalizes_pasted_full_urls_into_handle_columns(): void
    {
        $profile = new Profile([
            'instagram_handle' => 'https://instagram.com/example.user/',
            'facebook_slug' => 'https://facebook.com/example.page/',
            'linkedin_slug' => 'https://linkedin.com/company/example-brand/',
            'github_username' => 'https://github.com/example-user/',
        ]);

        $this->assertSame('example.user', $profile->getAttributes()['instagram_handle']);
        $this->assertSame('example.page', $profile->getAttributes()['facebook_slug']);
        $this->assertSame('company/example-brand', $profile->getAttributes()['linkedin_slug']);
        $this->assertSame('example-user', $profile->getAttributes()['github_username']);
    }

    public function test_profile_falls_back_to_legacy_url_columns_when_handles_are_missing(): void
    {
        $profile = new Profile([
            'instagram_handle' => null,
            'facebook_slug' => null,
            'linkedin_slug' => null,
            'instagram' => 'https://instagram.com/legacy.user',
            'facebook' => 'https://facebook.com/legacy.page',
            'linkedin' => 'https://linkedin.com/in/legacy-user',
        ]);

        $this->assertSame('https://instagram.com/legacy.user', $profile->instagram);
        $this->assertSame('https://facebook.com/legacy.page', $profile->facebook);
        $this->assertSame('https://linkedin.com/in/legacy-user', $profile->linkedin);
    }

    public function test_profile_rejects_non_platform_social_urls_when_normalizing_handles(): void
    {
        $profile = new Profile([
            'instagram_handle' => 'https://evilinstagram.com/example.user/',
            'facebook_slug' => 'https://facebook.example.com/example.page/',
            'linkedin_slug' => 'https://example.com/company/example-brand/',
            'github_username' => 'https://github.example.com/example-user/',
        ]);

        $this->assertNull($profile->getAttributes()['instagram_handle']);
        $this->assertNull($profile->getAttributes()['facebook_slug']);
        $this->assertNull($profile->getAttributes()['linkedin_slug']);
        $this->assertNull($profile->getAttributes()['github_username']);
    }
}
