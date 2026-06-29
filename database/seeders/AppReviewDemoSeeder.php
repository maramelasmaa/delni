<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AppReviewDemoSeeder extends Seeder
{
    private const CITIES = [
        ['slug' => 'tripoli', 'name' => 'Tripoli', 'icon' => 'heroicon-o-map-pin'],
        ['slug' => 'benghazi', 'name' => 'Benghazi', 'icon' => 'heroicon-o-map-pin'],
        ['slug' => 'misrata', 'name' => 'Misrata', 'icon' => 'heroicon-o-map-pin'],
        ['slug' => 'zawiya', 'name' => 'Zawiya', 'icon' => 'heroicon-o-map-pin'],
        ['slug' => 'sabha', 'name' => 'Sabha', 'icon' => 'heroicon-o-map-pin'],
        ['slug' => 'bayda', 'name' => 'Bayda', 'icon' => 'heroicon-o-map-pin'],
        ['slug' => 'tobruk', 'name' => 'Tobruk', 'icon' => 'heroicon-o-map-pin'],
        ['slug' => 'zliten', 'name' => 'Zliten', 'icon' => 'heroicon-o-map-pin'],
    ];

    private const CATEGORIES = [
        [
            'slug' => 'app-review-services',
            'name' => 'Professional Services',
            'subcategories' => [
                ['slug' => 'app-review-consulting', 'name' => 'Consulting'],
                ['slug' => 'business-consulting', 'name' => 'Business Consulting'],
                ['slug' => 'accounting', 'name' => 'Accounting'],
            ],
        ],
        [
            'slug' => 'digital-marketing',
            'name' => 'Digital Marketing',
            'subcategories' => [
                ['slug' => 'social-media-management', 'name' => 'Social Media Management'],
                ['slug' => 'paid-ads', 'name' => 'Paid Ads'],
                ['slug' => 'seo', 'name' => 'SEO'],
            ],
        ],
        [
            'slug' => 'design',
            'name' => 'Design',
            'subcategories' => [
                ['slug' => 'logo-design', 'name' => 'Logo Design'],
                ['slug' => 'branding', 'name' => 'Branding'],
                ['slug' => 'print-design', 'name' => 'Print Design'],
            ],
        ],
        [
            'slug' => 'technology',
            'name' => 'Technology',
            'subcategories' => [
                ['slug' => 'web-development', 'name' => 'Web Development'],
                ['slug' => 'mobile-app-development', 'name' => 'Mobile App Development'],
                ['slug' => 'technical-support', 'name' => 'Technical Support'],
            ],
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call(ProviderTypesSeeder::class);

        foreach (['user', 'provider', 'app_review_moderator'] as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $city = $this->seedCities()['tripoli'];
        [$category, $subcategory] = $this->seedCategories();

        $this->demoUser('reviewer-user@delni.ly', 'AppReviewDemo2026!', 'Apple Review Demo User', 'user');

        $providerUser = $this->demoUser(
            'reviewer-provider@delni.ly',
            'ProviderDemo2026!',
            'Apple Review Demo Provider Owner',
            'provider',
            '+218910000001',
        );

        $this->demoUser(
            'reviewer-admin@delni.ly',
            'ModeratorDemo2026!',
            'Apple Review Demo Moderator',
            'app_review_moderator',
        );

        $seededAuthor = $this->demoUser(
            'reviewer-seeded-author@delni.ly',
            'SeededReviewDemo2026!',
            'Apple Review Seeded Reviewer',
            'user',
        );

        $profile = Profile::withTrashed()->updateOrCreate(
            ['user_id' => $providerUser->id],
            [
                'business_name' => 'Apple Review Demo Provider',
                'type' => 'business',
                'provider_type' => 'company',
                'bio' => 'A realistic demo provider profile for Apple App Review. The profile is stable, public, and safe to use for browsing, favorites, reviews, and report moderation testing.',
                'slug' => 'apple-review-demo-provider',
                'offers_remote_work' => true,
                'service_area_note' => 'Available in Tripoli and remotely for App Review testing.',
                'city_id' => $city->id,
                'category_id' => $category->id,
                'phone' => '+218910000001',
                'whatsapp' => '+218910000000',
                'is_complete' => true,
                'provider_access_ends_at' => now()->addYear(),
            ],
        );

        if ($profile->trashed()) {
            $profile->restore();
        }

        $profile->subcategories()->syncWithoutDetaching([$subcategory->id]);

        $review = Review::withTrashed()->updateOrCreate(
            [
                'profile_id' => $profile->id,
                'user_id' => $seededAuthor->id,
            ],
            [
                'rating' => 5,
                'status' => ReviewStatus::APPROVED,
                'comment' => 'Helpful service and clear communication. This demo review is safe for Apple to report during App Review testing.',
                'is_flagged' => false,
                'flagged_by' => null,
                'flagged_at' => null,
                'flagged_reason' => null,
                'flag_handled_at' => null,
                'flag_handled_by' => null,
                'moderated_by' => null,
                'moderated_at' => null,
                'moderation_note' => null,
            ],
        );

        if ($review->trashed()) {
            $review->restore();
        }

        ProfileStats::updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'rating_avg' => 5.0,
                'reviews_count' => 1,
                'is_top_rated' => false,
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'is_top_search' => false,
                'top_search_until' => null,
                'is_top_category' => false,
                'top_category_until' => null,
                'is_top_subcategory' => false,
                'top_subcategory_until' => null,
            ],
        );

        $this->printInstructions();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function demoUser(string $email, string $password, string $name, string $role, ?string $phone = null): User
    {
        $user = User::withTrashed()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make($password),
                'is_active' => true,
                'is_suspended' => false,
                'security_flagged' => false,
                'email_verified_at' => now(),
            ],
        );

        if ($user->trashed()) {
            $user->restore();
        }

        $user->syncRoles([$role]);

        return $user;
    }

    /**
     * @return array<string, City>
     */
    private function seedCities(): array
    {
        $cities = [];

        foreach (self::CITIES as $cityData) {
            $city = City::withTrashed()->firstOrCreate(
                ['slug' => $cityData['slug']],
                [
                    'name' => $cityData['name'],
                    'name_ar' => $cityData['name'],
                    'icon' => $cityData['icon'],
                    'is_active' => true,
                ],
            );

            if ($city->trashed()) {
                $city->restore();
            }

            $city->forceFill([
                'name' => $cityData['name'],
                'name_ar' => $cityData['name'],
                'icon' => $cityData['icon'],
                'is_active' => true,
            ])->save();

            $cities[$cityData['slug']] = $city;
        }

        return $cities;
    }

    /**
     * @return array{0: Category, 1: Subcategory}
     */
    private function seedCategories(): array
    {
        $demoCategory = null;
        $demoSubcategory = null;

        foreach (self::CATEGORIES as $sort => $categoryData) {
            $category = Category::withTrashed()->firstOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'name_ar' => $categoryData['name'],
                    'sort_order' => $sort + 100,
                    'is_active' => true,
                ],
            );

            if ($category->trashed()) {
                $category->restore();
            }

            $category->forceFill([
                'name' => $categoryData['name'],
                'name_ar' => $categoryData['name'],
                'sort_order' => $sort + 100,
                'is_active' => true,
            ])->save();

            foreach ($categoryData['subcategories'] as $subSort => $subcategoryData) {
                $subcategory = Subcategory::withTrashed()->firstOrCreate(
                    ['slug' => $subcategoryData['slug']],
                    [
                        'category_id' => $category->id,
                        'name' => $subcategoryData['name'],
                        'name_ar' => $subcategoryData['name'],
                        'search_name' => strtolower($subcategoryData['name']),
                        'sort_order' => $subSort + 100,
                        'is_active' => true,
                    ],
                );

                if ($subcategory->trashed()) {
                    $subcategory->restore();
                }

                $subcategory->forceFill([
                    'category_id' => $category->id,
                    'name' => $subcategoryData['name'],
                    'name_ar' => $subcategoryData['name'],
                    'search_name' => strtolower($subcategoryData['name']),
                    'sort_order' => $subSort + 100,
                    'is_active' => true,
                ])->save();

                if ($categoryData['slug'] === 'app-review-services' && $subcategoryData['slug'] === 'app-review-consulting') {
                    $demoCategory = $category;
                    $demoSubcategory = $subcategory;
                }
            }
        }

        return [$demoCategory, $demoSubcategory];
    }

    private function printInstructions(): void
    {
        $this->command?->newLine();
        $this->command?->info('App Review demo access is ready.');
        $this->command?->line('Mobile demo user: reviewer-user@delni.ly / AppReviewDemo2026!');
        $this->command?->line('Provider demo user: reviewer-provider@delni.ly / ProviderDemo2026!');
        $this->command?->line('Filament moderator: reviewer-admin@delni.ly / ModeratorDemo2026!');
        $this->command?->line('Filament URL: /cp/admin');
        $this->command?->line('Provider slug: apple-review-demo-provider');
        $this->command?->newLine();
        $this->command?->line('Apple steps:');
        $this->command?->line('1. Log into the iOS app with reviewer-user@delni.ly.');
        $this->command?->line('2. Open Apple Review Demo Provider and favorite/unfavorite it.');
        $this->command?->line('3. Add a review, then report the existing demo review.');
        $this->command?->line('4. Log into Filament at /cp/admin with reviewer-admin@delni.ly.');
        $this->command?->line('5. Open Reviews, filter flagged/unhandled reports, and resolve the report.');
        $this->command?->line('No 2FA, email verification, phone verification, or CAPTCHA is required for these demo accounts.');
    }
}
