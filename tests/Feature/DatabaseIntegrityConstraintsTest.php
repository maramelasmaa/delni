<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionValidationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DatabaseIntegrityConstraintsTest extends TestCase
{
    use RefreshDatabase;

    private City $city;

    private Category $category;

    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->city = City::create([
            'name' => 'Tripoli',
            'name_ar' => 'Tripoli',
            'slug' => 'tripoli',
            'is_active' => true,
        ]);
        $this->category = Category::create([
            'name' => 'Design',
            'name_ar' => 'Design',
            'slug' => 'design',
            'is_active' => true,
        ]);
        $this->plan = SubscriptionPlan::create([
            'name' => 'Monthly',
            'name_ar' => 'Monthly',
            'duration_months' => 1,
            'price_lyd' => 100,
            'is_active' => true,
        ]);
    }

    public function test_duplicate_profile_for_user_is_rejected_by_database(): void
    {
        $provider = $this->user('provider');
        $this->profile($provider, ['slug' => 'provider-one']);

        $this->expectException(QueryException::class);

        $this->profile($provider, ['slug' => 'provider-two']);
    }

    public function test_duplicate_profile_stats_for_profile_is_rejected_by_database(): void
    {
        $profile = $this->profile($this->user('provider'), ['slug' => 'stats-owner']);
        ProfileStats::create(['profile_id' => $profile->id]);

        $this->expectException(QueryException::class);

        ProfileStats::create(['profile_id' => $profile->id]);
    }

    public function test_duplicate_review_for_user_and_profile_is_rejected_by_database(): void
    {
        $profile = $this->profile($this->user('provider'), ['slug' => 'reviewed-provider']);
        $reviewer = $this->user('user');
        $payload = [
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
            'status' => ReviewStatus::APPROVED,
            'comment' => 'Good',
        ];

        Review::create($payload);

        $this->expectException(QueryException::class);

        Review::create($payload);
    }

    public function test_duplicate_profile_slug_is_rejected_by_database(): void
    {
        $this->profile($this->user('provider'), ['slug' => 'same-slug']);

        $this->expectException(QueryException::class);

        $this->profile($this->user('provider'), ['slug' => 'same-slug']);
    }

    public function test_overlapping_subscription_is_rejected_inside_locked_transaction(): void
    {
        $provider = $this->user('provider');
        $service = app(SubscriptionValidationService::class);

        $service->createForProvider($provider, [
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        $this->expectException(ValidationException::class);

        $service->createForProvider($provider, [
            'plan_id' => $this->plan->id,
            'starts_at' => now()->addDays(10)->toDateString(),
            'ends_at' => now()->addDays(40)->toDateString(),
        ]);
    }

    public function test_foreign_keys_prevent_orphan_reviews(): void
    {
        $this->expectException(QueryException::class);

        DB::table('reviews')->insert([
            'profile_id' => 999999,
            'user_id' => 999999,
            'rating' => 5,
            'status' => ReviewStatus::APPROVED->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_integrity_audit_detects_missing_stats_and_overlapping_active_subscriptions(): void
    {
        $provider = $this->user('provider');
        $this->profile($provider, ['slug' => 'missing-stats']);

        DB::table('subscriptions')->insert([
            [
                'user_id' => $provider->id,
                'plan_id' => $this->plan->id,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonth()->toDateString(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $provider->id,
                'plan_id' => $this->plan->id,
                'starts_at' => now()->addDays(10)->toDateString(),
                'ends_at' => now()->addDays(40)->toDateString(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->artisan('integrity:audit')
            ->assertFailed();
    }

    public function test_fresh_database_has_required_integrity_constraints(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->assertTrue(DB::getSchemaBuilder()->hasTable('profiles'));
            $this->assertTrue(DB::getSchemaBuilder()->hasTable('profile_stats'));
            $this->assertTrue(DB::getSchemaBuilder()->hasTable('reviews'));

            return;
        }

        $this->assertTrue(DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'profiles')
            ->where('CONSTRAINT_NAME', 'profiles_user_id_unique')
            ->exists());

        $this->assertTrue(DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'profiles')
            ->where('CONSTRAINT_NAME', 'profiles_slug_unique')
            ->exists());

        $this->assertTrue(DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'reviews')
            ->where('CONSTRAINT_NAME', 'reviews_profile_id_user_id_unique')
            ->exists());
    }

    private function user(string $role): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'is_suspended' => false,

        ]);

        $user->assignRole($role);

        return $user;
    }

    private function profile(User $user, array $attributes): Profile
    {
        return Profile::create(array_merge([
            'user_id' => $user->id,
            'business_name' => 'Provider '.$user->id,
            'bio' => 'Useful provider bio',
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'whatsapp' => '+218911234567',
            'phone' => '+218911234567',
            'is_complete' => true,
        ], $attributes));
    }
}
