<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Comprehensive marketplace placement seeder.
 *
 * Creates diverse providers across cities with different placement types,
 * ratings, and subscription statuses to demonstrate marketplace ranking.
 *
 * Categories:
 * - Design (تصميم)
 * - Photography (تصوير)
 * - Web Development (تطوير ويب)
 *
 * Placement Types:
 * 1. Homepage Featured (مميز في الصفحة الرئيسية) - Top tier
 * 2. Top Search (أعلى البحث) - High visibility on search
 * 3. Top Category (أعلى التصنيف) - High in category pages
 * 4. Featured (مميز) - Basic featured status
 * 5. Top Rated (الأعلى تقييماً) - Achieved through reviews (≥5 reviews, ≥4.5 rating)
 * 6. Normal (عادي) - No special placement
 * 7. Expired Placements - Should appear as normal
 * 8. Inactive/Hidden - Suspended or expired subscription
 */
class MarketplacePlacementSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminId = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@delni.ly'))->value('id');

        // Get city IDs
        $cities = [
            'tripoli' => City::where('slug', 'tripoli')->value('id'),
            'benghazi' => City::where('slug', 'benghazi')->value('id'),
            'misrata' => City::where('slug', 'misrata')->value('id'),
            'zawiya' => City::where('slug', 'zawiya')->value('id'),
            'derna' => City::where('slug', 'derna')->value('id'),
        ];

        // Get category IDs
        $categories = [
            'design' => Category::where('name', 'Graphic Design')->value('id'),
            'photography' => Category::where('name', 'Photography & Video')->value('id'),
            'tech' => Category::where('name', 'Tech & Software')->value('id'),
        ];

        // Get subscription plan IDs
        $monthlyPlan = Subscription::where('plan_id', 1)->value('plan_id') ?? 1;
        $yearlyPlan = Subscription::where('plan_id', 2)->value('plan_id') ?? 2;

        // Create reviewer users
        $reviewers = $this->createReviewers();

        // Provider definitions with placements
        $providers = $this->getProviderDefinitions($cities, $categories);

        // Create providers with their stats and reviews
        foreach ($providers as $data) {
            $this->createProviderWithPlacement(
                $data,
                $adminId,
                $reviewers,
                $monthlyPlan,
                $yearlyPlan
            );
        }

        $this->command->info('✅ Marketplace placement data seeded successfully!');
        $this->displaySummary();
    }

    /**
     * Create reviewer users for reviews
     *
     * @return array<User>
     */
    private function createReviewers(): array
    {
        $reviewerData = [
            ['name' => 'علي الفقيه', 'email' => 'ali.faqih@example.ly'],
            ['name' => 'نور الصالح', 'email' => 'nour.salih@example.ly'],
            ['name' => 'سارة الجرابي', 'email' => 'sarah.jarabi@example.ly'],
            ['name' => 'محمد الشريف', 'email' => 'muhammad.sharif@example.ly'],
            ['name' => 'فاطمة الزناتي', 'email' => 'fatima.zinati@example.ly'],
            ['name' => 'عمر البرغثي', 'email' => 'umar.barghathi@example.ly'],
        ];

        $reviewers = [];
        foreach ($reviewerData as $data) {
            $user = User::firstOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'phone' => '+218' . str_pad(rand(900000, 999999), 9, '0', STR_PAD_LEFT),
                'password' => Hash::make('Demo@1234'),
                'is_active' => true,
                'is_suspended' => false,
            ]);
            if (!$user->hasRole('user')) {
                $user->assignRole('user');
            }
            $reviewers[] = $user;
        }

        return $reviewers;
    }

    /**
     * Define all providers with their placement details
     *
     * @return array<array>
     */
    private function getProviderDefinitions(array $cities, array $categories): array
    {
        return [
            // ===== HOMEPAGE FEATURED (مميز في الصفحة الرئيسية) =====
            [
                'user' => ['name' => 'أحمد الدغيم', 'email' => 'ahmad.dughim@example.ly'],
                'profile' => ['business_name' => 'استديو الدغيم للتصميم', 'city' => $cities['tripoli'], 'category' => $categories['design']],
                'placement' => ['is_homepage_featured' => true, 'homepage_featured_until' => Carbon::tomorrow()->addMonths(3)],
                'rating' => 4.8,
                'reviews' => 12,
                'plan' => 2,
                'active' => true,
            ],
            [
                'user' => ['name' => 'ليلى الشرقاوي', 'email' => 'layla.sharqawi@example.ly'],
                'profile' => ['business_name' => 'استوديو ليلى للتصوير', 'city' => $cities['benghazi'], 'category' => $categories['photography']],
                'placement' => ['is_homepage_featured' => true, 'homepage_featured_until' => Carbon::tomorrow()->addMonths(2)],
                'rating' => 4.9,
                'reviews' => 18,
                'plan' => 2,
                'active' => true,
            ],

            // ===== TOP SEARCH (أعلى البحث) =====
            [
                'user' => ['name' => 'عبدالقادر الفيتوري', 'email' => 'abdelqader.fitouri@example.ly'],
                'profile' => ['business_name' => 'تصاميم الفيتوري المبدعة', 'city' => $cities['misrata'], 'category' => $categories['design']],
                'placement' => ['is_top_search' => true, 'top_search_until' => Carbon::tomorrow()->addMonths(1)],
                'rating' => 4.6,
                'reviews' => 9,
                'plan' => 2,
                'active' => true,
            ],
            [
                'user' => ['name' => 'رضا الجديد', 'email' => 'reda.jaded@example.ly'],
                'profile' => ['business_name' => 'حلول رضا التقنية', 'city' => $cities['zawiya'], 'category' => $categories['tech']],
                'placement' => ['is_top_search' => true, 'top_search_until' => Carbon::tomorrow()->addMonths(2)],
                'rating' => 4.7,
                'reviews' => 15,
                'plan' => 2,
                'active' => true,
            ],

            // ===== TOP CATEGORY (أعلى التصنيف) =====
            [
                'user' => ['name' => 'مليحة الجويلي', 'email' => 'maliha.jwayli@example.ly'],
                'profile' => ['business_name' => 'استوديو مليحة المحترف', 'city' => $cities['tripoli'], 'category' => $categories['photography']],
                'placement' => ['is_top_category' => true, 'top_category_until' => Carbon::tomorrow()->addMonths(3)],
                'rating' => 4.5,
                'reviews' => 11,
                'plan' => 1,
                'active' => true,
            ],
            [
                'user' => ['name' => 'محسن الزعيم', 'email' => 'muhsin.zaaim@example.ly'],
                'profile' => ['business_name' => 'تطوير محسن للتطبيقات', 'city' => $cities['benghazi'], 'category' => $categories['tech']],
                'placement' => ['is_top_category' => true, 'top_category_until' => Carbon::tomorrow()->addMonths(1)],
                'rating' => 4.4,
                'reviews' => 8,
                'plan' => 2,
                'active' => true,
            ],

            // ===== TOP SUBCATEGORY (أعلى الفئة الفرعية) =====
            [
                'user' => ['name' => 'فاطمة الغريب', 'email' => 'fatima.gharib@example.ly'],
                'profile' => ['business_name' => 'تصاميم فاطمة الفريدة', 'city' => $cities['misrata'], 'category' => $categories['design']],
                'placement' => ['is_top_subcategory' => true, 'top_subcategory_until' => Carbon::tomorrow()->addMonths(2)],
                'rating' => 4.3,
                'reviews' => 7,
                'plan' => 1,
                'active' => true,
            ],

            // ===== FEATURED (مميز) =====
            [
                'user' => ['name' => 'سعيد الميلودي', 'email' => 'saeed.miludi@example.ly'],
                'profile' => ['business_name' => 'صور سعيد الاحترافية', 'city' => $cities['zawiya'], 'category' => $categories['photography']],
                'placement' => ['is_featured' => true, 'featured_until' => Carbon::tomorrow()->addMonths(1)],
                'rating' => 4.2,
                'reviews' => 6,
                'plan' => 1,
                'active' => true,
            ],
            [
                'user' => ['name' => 'خالد الغزالي', 'email' => 'khaled.ghazali@example.ly'],
                'profile' => ['business_name' => 'حلول خالد الذكية', 'city' => $cities['derna'], 'category' => $categories['tech']],
                'placement' => ['is_featured' => true, 'featured_until' => Carbon::tomorrow()->addMonths(3)],
                'rating' => 4.1,
                'reviews' => 5,
                'plan' => 2,
                'active' => true,
            ],

            // ===== TOP RATED (الأعلى تقييماً) - No explicit placement, achieved through reviews =====
            [
                'user' => ['name' => 'أم علي المختار', 'email' => 'umali.mokhtar@example.ly'],
                'profile' => ['business_name' => 'تصاميم أم علي الفاخرة', 'city' => $cities['tripoli'], 'category' => $categories['design']],
                'placement' => [],
                'rating' => 4.8,
                'reviews' => 14,
                'plan' => 2,
                'active' => true,
            ],
            [
                'user' => ['name' => 'يوسف الزين', 'email' => 'yousif.zain@example.ly'],
                'profile' => ['business_name' => 'صور يوسف الرائعة', 'city' => $cities['benghazi'], 'category' => $categories['photography']],
                'placement' => [],
                'rating' => 4.7,
                'reviews' => 13,
                'plan' => 1,
                'active' => true,
            ],
            [
                'user' => ['name' => 'ياسمين الحاج', 'email' => 'yasmin.hajj@example.ly'],
                'profile' => ['business_name' => 'تطوير ياسمين المتقدم', 'city' => $cities['misrata'], 'category' => $categories['tech']],
                'placement' => [],
                'rating' => 4.6,
                'reviews' => 10,
                'plan' => 2,
                'active' => true,
            ],

            // ===== NORMAL PROVIDERS (عادي) =====
            [
                'user' => ['name' => 'محمود البركي', 'email' => 'mahmoud.barki@example.ly'],
                'profile' => ['business_name' => 'تصاميم محمود العادية', 'city' => $cities['zawiya'], 'category' => $categories['design']],
                'placement' => [],
                'rating' => 3.8,
                'reviews' => 4,
                'plan' => 1,
                'active' => true,
            ],
            [
                'user' => ['name' => 'دعاء الشاعري', 'email' => 'duaa.shaari@example.ly'],
                'profile' => ['business_name' => 'صور دعاء البسيطة', 'city' => $cities['tripoli'], 'category' => $categories['photography']],
                'placement' => [],
                'rating' => 3.5,
                'reviews' => 3,
                'plan' => 1,
                'active' => true,
            ],
            [
                'user' => ['name' => 'علاء النجار', 'email' => 'alaa.najjar@example.ly'],
                'profile' => ['business_name' => 'حلول علاء الأساسية', 'city' => $cities['benghazi'], 'category' => $categories['tech']],
                'placement' => [],
                'rating' => 3.2,
                'reviews' => 2,
                'plan' => 1,
                'active' => true,
            ],
            [
                'user' => ['name' => 'هند المصراتي', 'email' => 'hind.misrati@example.ly'],
                'profile' => ['business_name' => 'تصاميم هند الناشئة', 'city' => $cities['misrata'], 'category' => $categories['design']],
                'placement' => [],
                'rating' => 3.0,
                'reviews' => 1,
                'plan' => 1,
                'active' => true,
            ],
            [
                'user' => ['name' => 'عبدالعزيز الملياني', 'email' => 'abdelaziz.miliani@example.ly'],
                'profile' => ['business_name' => 'صور عبدالعزيز الجديدة', 'city' => $cities['derna'], 'category' => $categories['photography']],
                'placement' => [],
                'rating' => 2.5,
                'reviews' => 2,
                'plan' => 1,
                'active' => true,
            ],

            // ===== EXPIRED PLACEMENTS (should appear as NORMAL) =====
            [
                'user' => ['name' => 'فريد البحراني', 'email' => 'fareed.bahrrani@example.ly'],
                'profile' => ['business_name' => 'تصاميم فريد المنتهية', 'city' => $cities['zawiya'], 'category' => $categories['design']],
                'placement' => ['is_homepage_featured' => true, 'homepage_featured_until' => Carbon::yesterday()],
                'rating' => 4.0,
                'reviews' => 6,
                'plan' => 1,
                'active' => true,
            ],
            [
                'user' => ['name' => 'ثريا الشرقاوي', 'email' => 'thuraya.sharqawi@example.ly'],
                'profile' => ['business_name' => 'صور ثريا المنتهية', 'city' => $cities['tripoli'], 'category' => $categories['photography']],
                'placement' => ['is_top_search' => true, 'top_search_until' => Carbon::now()->subDays(5)],
                'rating' => 3.9,
                'reviews' => 5,
                'plan' => 1,
                'active' => true,
            ],

            // ===== INACTIVE/HIDDEN (Suspended User) =====
            [
                'user' => ['name' => 'حسان المرزوقي', 'email' => 'hassan.marzouqi@example.ly'],
                'profile' => ['business_name' => 'تصاميم حسان المعلقة', 'city' => $cities['benghazi'], 'category' => $categories['design']],
                'placement' => [],
                'rating' => 4.5,
                'reviews' => 8,
                'plan' => 2,
                'active' => false,
                'suspended' => true,
            ],

            // ===== INACTIVE/HIDDEN (Expired Subscription) =====
            [
                'user' => ['name' => 'نادية الكويكبي', 'email' => 'nadia.kuwayki@example.ly'],
                'profile' => ['business_name' => 'صور نادية المنتهية الاشتراك', 'city' => $cities['misrata'], 'category' => $categories['photography']],
                'placement' => [],
                'rating' => 4.3,
                'reviews' => 7,
                'plan' => 1,
                'active' => false,
                'expired_subscription' => true,
            ],
        ];
    }

    /**
     * Create provider with profile, stats, subscription, and reviews
     */
    private function createProviderWithPlacement(
        array $data,
        ?int $adminId,
        array $reviewers,
        int $monthlyPlan,
        int $yearlyPlan
    ): void {
        // Create provider user
        $provider = User::firstOrCreate(['email' => $data['user']['email']], [
            'name' => $data['user']['name'],
            'phone' => '+218' . str_pad(rand(900000, 999999), 9, '0', STR_PAD_LEFT),
            'password' => Hash::make('Demo@1234'),
            'is_active' => true,
            'is_suspended' => $data['suspended'] ?? false,
        ]);
        if (!$provider->hasRole('provider')) {
            $provider->assignRole('provider');
        }

        // Create profile
        $slug = Str::slug($data['profile']['business_name']) . '-' . $provider->id;
        $profile = Profile::firstOrCreate(['user_id' => $provider->id], [
            'business_name' => $data['profile']['business_name'],
            'bio' => 'مزود خدمات احترافي متخصص في ' . $data['profile']['business_name'] . '. نقدم أفضل الخدمات بجودة عالية وأسعار منافسة.',
            'slug' => $slug,
            'city_id' => $data['profile']['city'],
            'category_id' => $data['profile']['category'],
            'whatsapp' => $provider->phone,
            'phone' => $provider->phone,
            'experience_years' => rand(2, 20),
            'is_complete' => true,
            'type' => 'business',
        ]);

        // Sync subcategories (just the first relevant subcategory)
        $profile->subcategories()->sync([1, 2, 3]);

        // Create/update profile stats with placement
        $isTopRated = $data['rating'] >= 4.5 && $data['reviews'] >= 5;
        $placementData = [
            'rating_avg' => $data['rating'],
            'reviews_count' => $data['reviews'],
            'is_top_rated' => $isTopRated,
            'is_featured' => $data['placement']['is_featured'] ?? false,
            'featured_until' => $data['placement']['featured_until'] ?? null,
            'is_homepage_featured' => $data['placement']['is_homepage_featured'] ?? false,
            'homepage_featured_until' => $data['placement']['homepage_featured_until'] ?? null,
            'is_top_search' => $data['placement']['is_top_search'] ?? false,
            'top_search_until' => $data['placement']['top_search_until'] ?? null,
            'is_top_category' => $data['placement']['is_top_category'] ?? false,
            'top_category_until' => $data['placement']['top_category_until'] ?? null,
            'is_top_subcategory' => $data['placement']['is_top_subcategory'] ?? false,
            'top_subcategory_until' => $data['placement']['top_subcategory_until'] ?? null,
        ];
        ProfileStats::updateOrCreate(['profile_id' => $profile->id], $placementData);

        // Create subscription
        $plan = $data['plan'] === 2 ? $yearlyPlan : $monthlyPlan;

        // Adjust for expired subscription (must start in past, end yesterday)
        if ($data['expired_subscription'] ?? false) {
            $startDate = Carbon::now()->subMonths(2);
            $endDate = Carbon::yesterday();
        } else {
            $startDate = Carbon::now();
            $endDate = $data['plan'] === 2
                ? $startDate->clone()->addYear()
                : $startDate->clone()->addMonth();
        }

        Subscription::firstOrCreate(
            ['user_id' => $provider->id, 'plan_id' => $plan],
            [
                'starts_at' => $startDate->toDateString(),
                'ends_at' => $endDate->toDateString(),
                'is_active' => !($data['expired_subscription'] ?? false),
                'approved_at' => now(),
                'approved_by' => $adminId,
                'processed_at' => now(),
                'processed_by' => $adminId,
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'REF-' . strtoupper(Str::random(8)),
                'payment_date' => now()->toDateString(),
            ]
        );

        // Create reviews (only for active providers with visible data)
        if ($data['active'] === true && $data['reviews'] > 0) {
            $this->createReviews($profile, $data['reviews'], $reviewers, $adminId);
        }
    }

    /**
     * Create approved reviews for a provider
     */
    private function createReviews(Profile $profile, int $count, array $reviewers, ?int $adminId): void
    {
        $ratingComments = [
            5 => [
                'ممتاز جداً، خدمة احترافية وسريعة. أنصح به بشدة.',
                'تعامل راقي وعمل متقن. سأعود مرة أخرى بالتأكيد.',
                'أفضل من تعاملت معه في هذا المجال. شكراً جزيلاً.',
            ],
            4 => [
                'عمل جيد جداً مع بعض التأخير البسيط.',
                'راضي عن النتيجة النهائية. سأتواصل مجدداً.',
                'خدمة احترافية، يمكن تحسين وقت الاستجابة.',
            ],
            3 => [
                'العمل مقبول لكن كنت أتوقع أفضل.',
                'تجربة عادية، لا بأس بها.',
            ],
            2 => [
                'لم أكن راضياً عن النتيجة النهائية.',
                'التواصل كان ضعيفاً وتأخر في التسليم.',
            ],
            1 => [
                'تجربة سيئة جداً، لا أنصح به.',
            ],
        ];

        $limit = min($count, count($reviewers));
        for ($i = 0; $i < $limit; $i++) {
            // Calculate rating based on average
            $targetRating = (int) round($profile->stats->rating_avg);
            $targetRating = max(1, min(5, $targetRating));

            // Vary slightly
            $rating = max(1, min(5, $targetRating + rand(-1, 1)));

            $comments = $ratingComments[$rating] ?? $ratingComments[3];
            $comment = $comments[array_rand($comments)];

            Review::firstOrCreate(
                ['profile_id' => $profile->id, 'user_id' => $reviewers[$i]->id],
                [
                    'rating' => $rating,
                    'comment' => $comment,
                    'status' => 'approved',
                    'moderated_by' => $adminId,
                    'moderated_at' => now()->subDays(rand(1, 14)),
                    'created_at' => now()->subDays(rand(15, 60)),
                    'updated_at' => now()->subDays(rand(1, 14)),
                ]
            );
        }
    }

    /**
     * Display summary of seeded data
     */
    private function displaySummary(): void
    {
        $this->command->table(
            ['📊 Metric', 'Count'],
            [
                ['Total Providers', User::role('provider')->count()],
                ['Active Profiles', Profile::count()],
                ['Homepage Featured', ProfileStats::where('is_homepage_featured', true)->whereDate('homepage_featured_until', '>=', Carbon::today())->count()],
                ['Top Search', ProfileStats::where('is_top_search', true)->whereDate('top_search_until', '>=', Carbon::today())->count()],
                ['Top Category', ProfileStats::where('is_top_category', true)->whereDate('top_category_until', '>=', Carbon::today())->count()],
                ['Top Subcategory', ProfileStats::where('is_top_subcategory', true)->whereDate('top_subcategory_until', '>=', Carbon::today())->count()],
                ['Featured', ProfileStats::where('is_featured', true)->whereDate('featured_until', '>=', Carbon::today())->count()],
                ['Top Rated (≥4.5 & ≥5 reviews)', ProfileStats::where('is_top_rated', true)->count()],
                ['Total Reviews', Review::count()],
                ['Approved Reviews', Review::where('status', 'approved')->count()],
                ['Suspended Providers', User::role('provider')->where('is_suspended', true)->count()],
                ['Expired Subscriptions', Subscription::whereDate('ends_at', '<', Carbon::today())->count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('🚀 Now visit:');
        $this->command->line('  • http://localhost:8080 - Homepage (featured providers)');
        $this->command->line('  • http://localhost:8080/search - Search (top search, featured)');
        $this->command->line('  • http://localhost:8080/category/graphic-design - Category ranking');
        $this->command->line('  • http://localhost:8080/providers/* - Individual provider pages');
    }
}
