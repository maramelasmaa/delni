<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Jobs\RecalculateProfileStatsJob;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use App\Services\ProfileStatsService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueDeploymentTest extends TestCase
{
    use RefreshDatabase;

    private City $city;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

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
    }

    public function test_review_approval_dispatches_recalculate_profile_stats_job(): void
    {
        $profile = $this->profile($this->user('provider'));
        $reviewer = $this->user('user');

        $review = Review::withoutEvents(fn (): Review => Review::create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
            'status' => ReviewStatus::PENDING,
        ]));

        Queue::fake([RecalculateProfileStatsJob::class]);

        $review->update(['status' => ReviewStatus::APPROVED]);

        Queue::assertPushed(
            RecalculateProfileStatsJob::class,
            fn (RecalculateProfileStatsJob $job): bool => $job->profileId === $profile->id,
        );
    }

    public function test_recalculate_profile_stats_job_updates_review_stats(): void
    {
        $profile = $this->profile($this->user('provider'));
        $reviewer = $this->user('user');

        Review::withoutEvents(fn (): Review => Review::create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
            'status' => ReviewStatus::APPROVED,
        ]));

        $job = new RecalculateProfileStatsJob($profile->id);
        $job->handle(app(ProfileStatsService::class));

        $stats = $profile->stats()->first();

        $this->assertSame(1, $stats->reviews_count);
        $this->assertSame('5.0', (string) $stats->rating_avg);
        $this->assertFalse($stats->is_top_rated);
    }

    private function user(string $role): User
    {
        $user = User::withoutEvents(fn (): User => User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,

        ]));

        $user->assignRole($role);

        return $user;
    }

    private function profile(User $user): Profile
    {
        $profile = Profile::withoutEvents(fn (): Profile => Profile::create([
            'user_id' => $user->id,
            'business_name' => 'Business '.$user->id,
            'bio' => 'Provider profile used by queue tests.',
            'slug' => 'queue-provider-'.$user->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'whatsapp' => '+218911234567',
            'phone' => '+218911234567',
            'is_complete' => true,
        ]));

        $profile->stats()->create([
            'rating_avg' => 0,
            'reviews_count' => 0,
            'is_top_rated' => false,
            'is_featured' => false,
            'is_homepage_featured' => false,
            'is_top_search' => false,
            'is_top_category' => false,
            'is_top_subcategory' => false,
        ]);

        return $profile;
    }
}
