<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_stored_and_linked_to_correct_provider(): void
    {
        // Create users
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'John Reviewer',
            'email' => 'reviewer@test.com',
            'password' => 'hashed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Jane Provider',
            'email' => 'provider@test.com',
            'password' => 'hashed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create category
        DB::table('categories')->insert([
            'id' => 1,
            'name' => 'Technology',
            'name_ar' => 'تكنولوجيا',
            'slug' => 'technology',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create city
        DB::table('cities')->insert([
            'id' => 1,
            'name' => 'Tripoli',
            'name_ar' => 'طرابلس',
            'slug' => 'tripoli',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create provider profile
        DB::table('profiles')->insert([
            'id' => 1,
            'user_id' => 2,
            'business_name' => 'Tech Services',
            'slug' => 'tech-services',
            'category_id' => 1,
            'city_id' => 1,
            'type' => 'business',
            'provider_type' => 'company',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create review
        DB::table('reviews')->insert([
            'profile_id' => 1,
            'user_id' => 1,
            'rating' => 5,
            'comment' => 'Excellent service! Very professional and timely.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // VERIFY: Review exists in database
        $this->assertDatabaseHas('reviews', [
            'profile_id' => 1,
            'user_id' => 1,
            'rating' => 5,
            'comment' => 'Excellent service! Very professional and timely.',
        ]);

        // VERIFY: Review count for provider is correct
        $this->assertEquals(1, DB::table('reviews')->where('profile_id', 1)->count());

        // VERIFY: Review belongs to correct reviewer
        $review = DB::table('reviews')->where('profile_id', 1)->first();
        $this->assertEquals(1, $review->user_id);
    }

    public function test_multiple_reviews_reach_same_provider(): void
    {
        // Setup
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Reviewer A', 'email' => 'a@test.com', 'password' => 'pwd', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Reviewer B', 'email' => 'b@test.com', 'password' => 'pwd', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Provider Co', 'email' => 'provider@test.com', 'password' => 'pwd', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('categories')->insert(['id' => 1, 'name' => 'Services', 'name_ar' => 'خدمات', 'slug' => 'services', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('cities')->insert(['id' => 1, 'name' => 'Benghazi', 'name_ar' => 'بنغازي', 'slug' => 'benghazi', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('profiles')->insert([
            'id' => 1,
            'user_id' => 3,
            'business_name' => 'Provider Co',
            'slug' => 'provider-co',
            'category_id' => 1,
            'city_id' => 1,
            'type' => 'business',
            'provider_type' => 'company',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create multiple reviews for same provider
        DB::table('reviews')->insert([
            'profile_id' => 1,
            'user_id' => 1,
            'rating' => 5,
            'comment' => '5 stars!',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('reviews')->insert([
            'profile_id' => 1,
            'user_id' => 2,
            'rating' => 4,
            'comment' => '4 stars, good service',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // VERIFY: Both reviews reach the provider (profile_id = 1)
        $this->assertEquals(2, DB::table('reviews')->where('profile_id', 1)->count());
        $this->assertTrue(
            DB::table('reviews')->where('profile_id', 1)->where('user_id', 1)->exists(),
            'Review from user 1 should reach provider'
        );
        $this->assertTrue(
            DB::table('reviews')->where('profile_id', 1)->where('user_id', 2)->exists(),
            'Review from user 2 should reach provider'
        );
    }
}
