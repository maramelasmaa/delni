<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\ProfileStats;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_visit_favorites_page_without_error(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])->get(route('api.favorites.index'));
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_visit_favorites_page_with_favorites(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $user->assignRole('user');

        $provider = $this->createProvider([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $profile = $provider->profile;
        $profile->update([
            'business_name' => 'Fav Provider',
            'type' => 'business',
            'offers_remote_work' => false,
            'bio' => 'Some bio text',
            'city_id' => City::factory()->create()->id,
            'category_id' => Category::factory()->create()->id,
            'whatsapp' => '+218910000000',
            'phone' => '+218910000000',
            'is_complete' => true,
        ]);

        ProfileStats::firstOrCreate(['profile_id' => $profile->id]);

        UserFavorite::create([
            'user_id' => $user->id,
            'profile_id' => $profile->id,
        ]);

        $this->actingAs($user);

        $response = $this->withHeaders(['Accept' => 'application/json'])->get(route('api.favorites.index'));
        $response->assertOk();
    }
}
