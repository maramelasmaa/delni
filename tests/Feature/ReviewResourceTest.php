<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReviewResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_direct_moderation_actions_are_visible_for_unflagged_approved_reviews(): void
    {
        $review = Review::factory()->create([
            'status' => ReviewStatus::APPROVED,
            'is_flagged' => false,
            'deleted_at' => null,
        ]);

        $this->assertTrue(ReviewResource::canShowDirectModerationAction($review->fresh()));
    }

    public function test_direct_moderation_actions_are_hidden_for_pending_reviews(): void
    {
        $review = Review::factory()->create([
            'status' => ReviewStatus::PENDING,
            'is_flagged' => false,
        ]);

        $this->assertFalse(ReviewResource::canShowDirectModerationAction($review->fresh()));
    }

    public function test_direct_moderation_actions_are_hidden_for_flagged_reviews(): void
    {
        $review = Review::factory()->create([
            'status' => ReviewStatus::APPROVED,
            'is_flagged' => true,
        ]);

        $this->assertFalse(ReviewResource::canShowDirectModerationAction($review->fresh()));
    }
}
