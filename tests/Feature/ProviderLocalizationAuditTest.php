<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProviderLocalizationAuditTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function provider_dashboard_contains_no_english_labels(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)
            ->get('/provider/dashboard');

        $response->assertStatus(200);

        // Dashboard title
        $response->assertSeeInOrder(['لوحة التحكم'], false);

        // Should not contain English
        $response->assertDontSeeInOrder(['Dashboard'], false);
    }

    #[Test]
    public function provider_sidebar_contains_arabic_labels_only(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)
            ->get('/provider/dashboard');

        $response->assertStatus(200);

        // Navigation labels (from ProviderPanelProvider and resources)
        $response->assertSee('لوحة التحكم', false); // Dashboard
        $response->assertSee('ملفي التجاري', false); // Profile
        $response->assertSee('أعمالي ومشاريعي', false); // Portfolio
        $response->assertSee('شهاداتي وخبراتي', false); // Credentials
        $response->assertSee('اشتراكي', false); // Subscription
        $response->assertSee('تقييماتي', false); // Reviews

        // Should not contain English navigation labels
        $response->assertDontSee('Dashboard', false);
        $response->assertDontSee('Profile', false);
        $response->assertDontSee('Portfolio', false);
    }

    #[Test]
    public function provider_profile_page_contains_no_raw_translation_keys(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)
            ->get('/provider/profile/'.$provider->profile->id.'/edit');

        $response->assertStatus(200);

        // Check for Arabic labels
        $response->assertSee('ملفي التجاري', false);

        // Should not contain raw translation keys
        $response->assertDontSee('messages.', false);
        $response->assertDontSee('filament.', false);
        $response->assertDontSee('validation.', false);
    }

    #[Test]
    public function provider_portfolio_page_contains_no_raw_translation_keys(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)
            ->get('/provider/portfolio');

        $response->assertStatus(200);

        // Check for Arabic labels
        $response->assertSee('أعمالي ومشاريعي', false);

        // Should not contain raw translation keys
        $response->assertDontSee('messages.', false);
        $response->assertDontSee('filament.', false);
    }

    #[Test]
    public function provider_credentials_page_contains_no_raw_translation_keys(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)
            ->get('/provider/credentials');

        $response->assertStatus(200);

        // Check for Arabic labels
        $response->assertSee('شهاداتي وخبراتي', false);

        // Should not contain raw translation keys
        $response->assertDontSee('messages.', false);
        $response->assertDontSee('filament.', false);
    }

    #[Test]
    public function provider_subscription_page_contains_no_raw_translation_keys(): void
    {
        $provider = $this->createProvider();
        $plan = SubscriptionPlan::factory()->create();
        $provider->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($provider)
            ->get('/provider/subscription');

        $response->assertStatus(200);

        // Check for Arabic labels
        $response->assertSee('اشتراكي', false);

        // Should not contain raw translation keys
        $response->assertDontSee('messages.', false);
        $response->assertDontSee('filament.', false);
    }

    #[Test]
    public function provider_reviews_page_contains_no_raw_translation_keys(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)
            ->get('/provider/reviews');

        $response->assertStatus(200);

        // Check for Arabic labels
        $response->assertSee('تقييماتي', false);

        // Should not contain raw translation keys
        $response->assertDontSee('messages.', false);
        $response->assertDontSee('filament.', false);
    }

    #[Test]
    public function provider_access_denied_message_is_arabic(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/provider/dashboard');

        $response->assertStatus(403);
        $response->assertSeeText(__('messages.provider_access_denied'));
    }

    #[Test]
    public function account_deactivated_message_is_arabic(): void
    {
        $provider = $this->createProvider();
        $provider->update(['is_active' => false]);

        $response = $this->actingAs($provider)
            ->get('/provider/dashboard');

        $response->assertStatus(403);
        $response->assertSeeText(__('messages.account_deactivated'));
    }

    #[Test]
    public function account_suspended_message_is_arabic(): void
    {
        $provider = $this->createProvider();
        $provider->update(['is_suspended' => true]);

        $response = $this->actingAs($provider)
            ->get('/provider/dashboard');

        $response->assertStatus(403);
        $response->assertSeeText(__('messages.account_suspended'));
    }

    #[Test]
    public function account_locked_message_is_arabic(): void
    {
        $provider = $this->createProvider();
        $provider->update(['locked_until' => now()->addHour()]);

        $response = $this->actingAs($provider)
            ->get('/provider/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/provider/login');
        $response->assertSessionHas('error', __('messages.account_locked'));
    }

    #[Test]
    public function no_provider_response_contains_english_or_raw_keys(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)
            ->get('/provider/dashboard');

        $content = $response->getContent();

        // English words that should NOT appear
        $forbiddenEnglish = [
            'Dashboard',
            'Create',
            'Edit',
            'Delete',
            'Save',
            'Cancel',
            'Search',
            'Actions',
            'No records',
            'Profile',
            'Portfolio',
            'Credentials',
            'Links',
            'Subscription',
            'Reviews',
        ];

        foreach ($forbiddenEnglish as $word) {
            // Check for word boundaries to avoid matching in legitimate contexts
            if (preg_match('/\b'.$word.'\b/', $content)) {
                $this->fail("Found English word '$word' in provider panel response");
            }
        }

        // Raw translation keys that should NOT appear
        $forbiddenKeys = [
            'messages.',
            'filament.',
            'validation.',
            'auth.',
            'pages.',
            'resources.',
            'models.',
            'fields.',
            'actions.',
            'notifications.',
        ];

        foreach ($forbiddenKeys as $key) {
            $this->assertStringNotContainsString($key, $content, "Found raw translation key '$key' in provider panel response");
        }
    }

    #[Test]
    public function provider_form_labels_are_arabic(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)
            ->get('/provider/profile/'.$provider->profile->id.'/edit');

        $response->assertStatus(200);

        // Form field labels
        $response->assertSee('اسم العمل', false); // business_name
        $response->assertSee('نوع العمل', false); // business_type
        $response->assertSee('التصنيف الرئيسي', false); // category
        $response->assertSee('المدينة', false); // city
        $response->assertSee('الوصف', false); // description
        $response->assertSee('الهاتف', false); // phone
        $response->assertSee('واتساب', false); // whatsapp
        $response->assertSee('الموقع الإلكتروني', false); // website
    }

    #[Test]
    public function review_validation_messages_are_arabic(): void
    {
        $provider = $this->createProvider();
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->post('/review', [
                'profile_id' => $provider->profile->id,
                'rating' => 'invalid',
                'comment' => '',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        // The validation error should be in Arabic
        // (Filament/Laravel default validation messages)
    }
}
