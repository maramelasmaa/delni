<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\RecalculateProfileStatsJob;
use App\Models\ActivityLog;
use App\Models\Profile;
use App\Models\ProviderLink;
use App\Models\Review;
use App\Models\Subscription;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ObserverSafetyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that subscription creation is safe via SubscriptionLifecycleService
     */
    public function test_subscription_lifecycle_service_ensures_creation_validity(): void
    {
        $provider = $this->createProvider();
        $subscription = new Subscription([
            'user_id' => $provider->id,
            'plan_id' => 1,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $service = app(SubscriptionLifecycleService::class);
        $service->prepareForCreation($subscription);

        $this->assertTrue($subscription->is_active);
        $this->assertNotNull($subscription->approved_at);
    }

    /**
     * Test that immutable fields are enforced on updates
     */
    public function test_subscription_immutability_enforced(): void
    {
        $provider = $this->createProvider();
        $subscription = Subscription::factory()->create(['user_id' => $provider->id]);

        $service = app(SubscriptionLifecycleService::class);
        $subscription->user_id = 999;

        $this->expectException(ValidationException::class);
        $service->assertImmutableFieldsUnchanged($subscription);
    }

    /**
     * Test that review observer dispatches stats job
     */
    public function test_review_observer_dispatches_stats_job(): void
    {
        Queue::fake();

        $profile = Profile::factory()->create();
        $review = Review::factory()->create(['profile_id' => $profile->id]);

        Queue::assertPushed(RecalculateProfileStatsJob::class);
    }

    /**
     * Test that profile restoration updates normalized columns
     */
    public function test_profile_restoration_updates_normalized_columns(): void
    {
        $profile = Profile::factory()->create(['business_name' => 'أحمد']);

        $profile->delete();
        $profile->restore();

        $profile->refresh();
        $this->assertNotNull($profile->search_business_name);
    }

    /**
     * Test provider link limits enforced
     */
    public function test_provider_asset_limit_prevents_overflow(): void
    {
        $profile = Profile::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            ProviderLink::create([
                'profile_id' => $profile->id,
                'title' => "Link {$i}",
                'url' => "https://example.com/{$i}",
                'is_active' => true,
            ]);
        }

        $this->expectException(ValidationException::class);
        ProviderLink::create([
            'profile_id' => $profile->id,
            'title' => 'Link 11',
            'url' => 'https://example.com/11',
            'is_active' => true,
        ]);
    }

    /**
     * Test review observer logs status changes
     */
    public function test_review_observer_logs_status_changes(): void
    {
        $review = Review::factory()->create(['status' => 'pending']);

        $review->update(['status' => 'approved']);

        $this->assertDatabaseHas(ActivityLog::class, [
            'subject_type' => Review::class,
            'subject_id' => $review->id,
            'action' => 'review_moderated',
        ]);
    }
}
