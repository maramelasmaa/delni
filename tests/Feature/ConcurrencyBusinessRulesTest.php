<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioImage;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewCreationService;
use App\Services\ReviewModerationService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ConcurrencyBusinessRulesTest extends TestCase
{
    public function test_review_creation_service_rejects_duplicate_review_without_creating_second_row(): void
    {
        Queue::fake();

        $profile = $this->makeVisibleProfile();
        $reviewer = $this->makeEligibleReviewer();
        $service = app(ReviewCreationService::class);

        $service->create($reviewer, $profile, 5, 'First review');

        $this->expectException(ValidationException::class);

        try {
            $service->create($reviewer, $profile, 4, 'Duplicate review');
        } finally {
            $this->assertSame(1, Review::where('profile_id', $profile->id)->where('user_id', $reviewer->id)->count());
        }
    }

    public function test_review_creation_service_enforces_daily_limit_in_write_path(): void
    {
        Queue::fake();

        $reviewer = $this->makeEligibleReviewer();

        Review::factory()
            ->count(10)
            ->for($reviewer)
            ->create(['created_at' => now()]);

        $this->expectException(ValidationException::class);

        app(ReviewCreationService::class)->create(
            user: $reviewer,
            profile: $this->makeVisibleProfile(),
            rating: 5,
            comment: 'One too many',
        );

        $this->assertSame(10, Review::where('user_id', $reviewer->id)->count());
    }

    public function test_portfolio_item_limit_cannot_exceed_two(): void
    {
        $profile = $this->makeVisibleProfile();

        PortfolioItem::factory()->count(2)->create(['profile_id' => $profile->id]);

        $this->expectException(ValidationException::class);

        try {
            PortfolioItem::factory()->create(['profile_id' => $profile->id]);
        } finally {
            $this->assertSame(2, PortfolioItem::where('profile_id', $profile->id)->count());
        }
    }

    public function test_portfolio_image_limit_cannot_exceed_four(): void
    {
        $item = PortfolioItem::factory()->create([
            'profile_id' => $this->makeVisibleProfile()->id,
        ]);

        PortfolioImage::factory()->count(4)->create(['portfolio_item_id' => $item->id]);

        $this->expectException(ValidationException::class);

        try {
            PortfolioImage::factory()->create(['portfolio_item_id' => $item->id]);
        } finally {
            $this->assertSame(4, PortfolioImage::where('portfolio_item_id', $item->id)->count());
        }
    }

    public function test_review_flag_decision_is_idempotent_after_first_admin_handles_it(): void
    {
        Queue::fake();

        $review = Review::factory()->create([
            'is_flagged' => true,
            'flagged_at' => now(),
            'flagged_by' => $this->makeEligibleReviewer()->id,
            'status' => ReviewStatus::APPROVED,
        ]);

        $service = app(ReviewModerationService::class);

        $service->acceptFlag($review);
        $handledAt = $review->fresh()->flag_handled_at;

        $service->rejectFlag($review->fresh());

        $review->refresh();

        $this->assertSame(ReviewStatus::REJECTED, $review->status);
        $this->assertTrue($review->is_flagged);
        $this->assertTrue($review->flag_handled_at->equalTo($handledAt));
    }

    private function makeEligibleReviewer(): User
    {
        return $this->createUser([
            'created_at' => now()->subDays(2),
            'is_active' => true,
            'is_suspended' => false,
        ]);
    }

    private function makeVisibleProfile(): Profile
    {
        $provider = $this->createProvider([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $profile = $provider->profile;
        $profile->update([
            'business_name' => 'Visible Provider',
            'type' => 'business',
            'provider_type' => 'company',
            'bio' => 'A complete profile for rule tests.',
            'city_id' => City::factory()->create()->id,
            'category_id' => Category::factory()->create()->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'experience_years' => 5,
            'is_complete' => true,
            'provider_access_ends_at' => now()->addMonth(),
        ]);

        ProfileStats::firstOrCreate(['profile_id' => $profile->id]);

        return $profile->fresh();
    }
}
