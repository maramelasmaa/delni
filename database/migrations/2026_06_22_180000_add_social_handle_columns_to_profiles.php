<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->string('instagram_handle')->nullable()->after('website');
            $table->string('facebook_slug')->nullable()->after('instagram_handle');
            $table->string('linkedin_slug')->nullable()->after('facebook_slug');
            $table->string('github_username')->nullable()->after('linkedin_slug');
        });

        DB::table('profiles')
            ->select(['id', 'instagram', 'facebook', 'linkedin'])
            ->orderBy('id')
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    $updates = [
                        'instagram_handle' => $this->extractInstagramHandle($profile->instagram),
                        'facebook_slug' => $this->extractSocialPath($profile->facebook, 'facebook.com'),
                        'linkedin_slug' => $this->extractSocialPath($profile->linkedin, 'linkedin.com'),
                    ];

                    if (array_filter($updates, fn ($value) => filled($value)) === []) {
                        continue;
                    }

                    DB::table('profiles')
                        ->where('id', $profile->id)
                        ->update($updates);
                }
            });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'instagram_handle',
                'facebook_slug',
                'linkedin_slug',
                'github_username',
            ]);
        });
    }

    private function extractInstagramHandle(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('#^https?://#i', $value) === 1) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $path = trim((string) parse_url($value, PHP_URL_PATH), '/');

            if (! $this->socialHostMatches($host, 'instagram.com')) {
                return null;
            }

            return $path !== '' ? ltrim((string) strtok($path, '/'), '@') : null;
        }

        return ltrim($value, '@');
    }

    private function extractSocialPath(?string $value, string $expectedHost): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('#^https?://#i', $value) === 1) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $path = trim((string) parse_url($value, PHP_URL_PATH), '/');

            if (! $this->socialHostMatches($host, $expectedHost)) {
                return null;
            }

            return $path !== '' ? $path : null;
        }

        return trim($value, '/');
    }

    private function socialHostMatches(?string $host, string $expectedHost): bool
    {
        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);
        $expectedHost = strtolower($expectedHost);

        return $host === $expectedHost || str_ends_with($host, '.'.$expectedHost);
    }
};
