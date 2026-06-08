<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminId = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@delni.ly'))->value('id');
        $now = now();

        // ── PROVIDERS ─────────────────────────────────────────────────────────
        $providers = [
            [
                'user' => ['name' => 'Ahmed Al-Mansouri',   'email' => 'ahmed@demo.ly',   'phone' => '+218912001001'],
                'profile' => [
                    'business_name' => 'Al-Mansouri Design Studio',
                    'bio' => 'Professional graphic design studio based in Tripoli. We specialize in brand identity, logo design, and social media content for Libyan businesses.',
                    'city_id' => 1,  // Tripoli
                    'category_id' => 1,  // Graphic Design
                    'subcategory_ids' => [1, 2], // Logo Design, Social Media
                    'whatsapp' => '+218912001001',
                    'experience_years' => 7,
                ],
                'plan_id' => 2, // Yearly
                'rating' => 4.8,
                'count' => 12,
                'featured' => true,
            ],
            [
                'user' => ['name' => 'Omar Al-Barghathi',   'email' => 'omar@demo.ly',    'phone' => '+218922002002'],
                'profile' => [
                    'business_name' => 'TechLibya Solutions',
                    'bio' => 'Full-stack web and mobile development company in Benghazi. We build modern, scalable applications for startups and enterprises across Libya.',
                    'city_id' => 2,  // Benghazi
                    'category_id' => 3,  // Tech & Software
                    'subcategory_ids' => [8, 9], // Web Dev, Mobile Apps
                    'whatsapp' => '+218922002002',
                    'experience_years' => 5,
                ],
                'plan_id' => 2, // Yearly
                'rating' => 4.6,
                'count' => 9,
                'featured' => true,
            ],
            [
                'user' => ['name' => 'Fatima Al-Zwai',      'email' => 'fatima@demo.ly',  'phone' => '+218913003003'],
                'profile' => [
                    'business_name' => 'Zwai Legal Consultancy',
                    'bio' => 'Licensed lawyer with 10 years of experience in commercial law, contracts, and business registration in Libya.',
                    'city_id' => 1,  // Tripoli
                    'category_id' => 5,  // Legal & Accounting
                    'subcategory_ids' => [15, 16], // Lawyer, Accountant
                    'whatsapp' => '+218913003003',
                    'experience_years' => 10,
                ],
                'plan_id' => 1, // Monthly
                'rating' => 4.3,
                'count' => 6,
                'featured' => false,
            ],
            [
                'user' => ['name' => 'Khalid Al-Warfalli',  'email' => 'khalid@demo.ly',  'phone' => '+218924004004'],
                'profile' => [
                    'business_name' => 'Warfalli Photography',
                    'bio' => 'Award-winning photographer covering weddings, corporate events, and product shoots across Libya. Over 500 weddings photographed.',
                    'city_id' => 3,  // Misrata
                    'category_id' => 4,  // Photography & Video
                    'subcategory_ids' => [12, 14], // Wedding, Video
                    'whatsapp' => '+218924004004',
                    'experience_years' => 8,
                ],
                'plan_id' => 1, // Monthly
                'rating' => 4.9,
                'count' => 18,
                'featured' => true,
            ],
            [
                'user' => ['name' => 'Mustapha Benali',     'email' => 'mustapha@demo.ly', 'phone' => '+218915005005'],
                'profile' => [
                    'business_name' => 'Benali Auto Workshop',
                    'bio' => 'Certified car mechanic and auto electrician in Zawiya. Specializing in European and Japanese cars. Fast, reliable service at honest prices.',
                    'city_id' => 4,  // Zawiya
                    'category_id' => 6,  // Auto & Mechanics
                    'subcategory_ids' => [18, 19], // Car Mechanic, Auto Electrician
                    'whatsapp' => '+218915005005',
                    'experience_years' => 12,
                ],
                'plan_id' => 1, // Monthly
                'rating' => 3.8,
                'count' => 5,
                'featured' => false,
            ],
            [
                'user' => ['name' => 'Ibrahim Al-Senussi',  'email' => 'ibrahim@demo.ly', 'phone' => '+218926006006'],
                'profile' => [
                    'business_name' => 'Al-Senussi Contracting',
                    'bio' => 'Licensed construction contractor in Benghazi. We build residential and commercial properties with quality materials and on-time delivery.',
                    'city_id' => 2,  // Benghazi
                    'category_id' => 2,  // Construction
                    'subcategory_ids' => [4, 6], // Building, Electrical
                    'whatsapp' => '+218926006006',
                    'experience_years' => 15,
                ],
                'plan_id' => 2, // Yearly
                'rating' => 4.1,
                'count' => 7,
                'featured' => false,
            ],
        ];

        // ── REVIEWERS ─────────────────────────────────────────────────────────
        $reviewers = [
            ['name' => 'Sami Elhaddad',    'email' => 'sami@demo.ly',    'phone' => '+218911100001'],
            ['name' => 'Nour Bensalem',    'email' => 'nour@demo.ly',    'phone' => '+218921100002'],
            ['name' => 'Youssef Mabrouk',  'email' => 'youssef@demo.ly', 'phone' => '+218931100003'],
            ['name' => 'Aisha Gaddafi',    'email' => 'aisha@demo.ly',   'phone' => '+218941100004'],
        ];

        $reviewerUsers = [];
        foreach ($reviewers as $r) {
            $user = User::firstOrCreate(['email' => $r['email']], [
                'name' => $r['name'],
                'phone' => $r['phone'],
                'password' => Hash::make('Demo@1234'),
                'is_active' => true,
                'is_suspended' => false,
            ]);
            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }
            $reviewerUsers[] = $user;
        }

        // ── CREATE PROVIDERS + PROFILES + SUBSCRIPTIONS + REVIEWS ─────────────
        $reviewComments = [
            5 => [
                'ممتاز جداً، خدمة احترافية وسريعة. أنصح به بشدة.',
                'تعامل راقي وعمل متقن. سأعود مرة أخرى بالتأكيد.',
                'أفضل من تعاملت معه في هذا المجال. شكراً جزيلاً.',
                'نتائج رائعة وبأسعار مناسبة. خمس نجوم تستحقها.',
                'التزام بالمواعيد وجودة عالية. أوصي به لكل أحد.',
            ],
            4 => [
                'عمل جيد جداً مع بعض التأخير البسيط.',
                'راضي عن النتيجة النهائية. سأتواصل مجدداً.',
                'خدمة احترافية، يمكن تحسين وقت الاستجابة.',
                'جودة ممتازة وسعر معقول.',
            ],
            3 => [
                'العمل مقبول لكن كنت أتوقع أفضل.',
                'تجربة عادية، لا بأس بها.',
                'الجودة متوسطة مقارنة بالسعر.',
            ],
            2 => [
                'لم أكن راضياً عن النتيجة النهائية.',
                'التواصل كان ضعيفاً وتأخر في التسليم.',
            ],
            1 => [
                'تجربة سيئة جداً، لا أنصح به.',
            ],
        ];

        foreach ($providers as $data) {
            // Create or find provider user
            $provider = User::firstOrCreate(['email' => $data['user']['email']], [
                'name' => $data['user']['name'],
                'phone' => $data['user']['phone'],
                'password' => Hash::make('Demo@1234'),
                'is_active' => true,
                'is_suspended' => false,
            ]);
            if (! $provider->hasRole('provider')) {
                $provider->assignRole('provider');
            }

            // Create profile
            $slug = Str::slug($data['profile']['business_name']).'-'.$provider->id;
            $profile = Profile::firstOrCreate(['user_id' => $provider->id], [
                'business_name' => $data['profile']['business_name'],
                'bio' => $data['profile']['bio'],
                'slug' => $slug,
                'city_id' => $data['profile']['city_id'],
                'category_id' => $data['profile']['category_id'],
                'whatsapp' => $data['profile']['whatsapp'],
                'phone' => $data['profile']['whatsapp'],
                'experience_years' => $data['profile']['experience_years'],
                'is_complete' => true,
                'type' => 'business',
            ]);

            // Sync subcategories
            $profile->subcategories()->sync($data['profile']['subcategory_ids']);

            // Create profile stats
            $topRated = $data['rating'] >= 4.5 && $data['count'] >= 5;
            ProfileStats::updateOrCreate(['profile_id' => $profile->id], [
                'rating_avg' => $data['rating'],
                'reviews_count' => $data['count'],
                'is_top_rated' => $topRated,
                'is_featured' => $data['featured'],
                'featured_until' => $data['featured'] ? now()->addMonths(2)->toDateString() : null,
            ]);

            // Create active approved subscription
            Subscription::firstOrCreate(
                ['user_id' => $provider->id, 'plan_id' => $data['plan_id']],
                [
                    'starts_at' => now()->toDateString(),
                    'ends_at' => now()->addMonths($data['plan_id'] === 2 ? 12 : 1)->toDateString(),
                    'is_active' => true,
                    'approved_at' => now(),
                    'approved_by' => $adminId,
                    'processed_at' => now(),
                    'processed_by' => $adminId,
                    'payment_method' => 'bank_transfer',
                    'payment_reference' => 'REF-'.strtoupper(Str::random(8)),
                    'payment_date' => now()->toDateString(),
                ]
            );

            // Create approved reviews from each reviewer
            $ratingDistribution = $this->buildRatingDistribution($data['rating'], $data['count']);
            $reviewIndex = 0;

            foreach ($reviewerUsers as $reviewer) {
                if ($reviewIndex >= count($ratingDistribution)) {
                    break;
                }

                $rating = $ratingDistribution[$reviewIndex];
                $comments = $reviewComments[$rating];

                // Skip if already reviewed
                if (Review::withTrashed()->where('profile_id', $profile->id)->where('user_id', $reviewer->id)->exists()) {
                    $reviewIndex++;

                    continue;
                }

                Review::create([
                    'profile_id' => $profile->id,
                    'user_id' => $reviewer->id,
                    'rating' => $rating,
                    'comment' => $comments[array_rand($comments)],
                    'status' => 'approved',
                    'moderated_by' => $adminId,
                    'moderated_at' => now()->subDays(rand(1, 14)),
                    'created_at' => now()->subDays(rand(15, 60)),
                    'updated_at' => now()->subDays(rand(1, 14)),
                ]);

                $reviewIndex++;
            }
        }

        // ── PENDING REVIEWS (not yet moderated) ───────────────────────────────
        $ahmedProfile = Profile::whereHas('user', fn ($q) => $q->where('email', 'ahmed@demo.ly'))->first();
        $omarProfile = Profile::whereHas('user', fn ($q) => $q->where('email', 'omar@demo.ly'))->first();

        $pendingData = [
            ['profile' => $ahmedProfile,  'user' => $reviewerUsers[2], 'rating' => 5, 'comment' => 'تصاميم احترافية جداً، أفضل مكتب تصميم في طرابلس!'],
            ['profile' => $omarProfile,   'user' => $reviewerUsers[3], 'rating' => 4, 'comment' => 'شركة ممتازة للتطوير التقني، أنصح بهم بشدة.'],
        ];

        foreach ($pendingData as $p) {
            if ($p['profile'] && ! Review::withTrashed()->where('profile_id', $p['profile']->id)->where('user_id', $p['user']->id)->exists()) {
                Review::create([
                    'profile_id' => $p['profile']->id,
                    'user_id' => $p['user']->id,
                    'rating' => $p['rating'],
                    'comment' => $p['comment'],
                    'status' => 'pending',
                    'created_at' => now()->subHours(rand(2, 48)),
                    'updated_at' => now()->subHours(rand(1, 24)),
                ]);
            }
        }

        // ── FLAGGED REVIEW ────────────────────────────────────────────────────
        $khProfile = Profile::whereHas('user', fn ($q) => $q->where('email', 'khalid@demo.ly'))->first();
        if ($khProfile) {
            $existingFlagged = Review::where('profile_id', $khProfile->id)
                ->where('is_flagged', true)->first();

            if (! $existingFlagged) {
                // Find an approved review on Khalid's profile to flag
                $reviewToFlag = Review::where('profile_id', $khProfile->id)
                    ->where('status', 'approved')
                    ->first();

                if ($reviewToFlag) {
                    $reviewToFlag->update([
                        'is_flagged' => true,
                        'flagged_by' => $reviewerUsers[0]->id,
                        'flagged_at' => now()->subHours(6),
                        'flagged_reason' => 'هذا التقييم يبدو مزيفاً ولا يعكس تجربة حقيقية.',
                    ]);
                }
            }
        }

        // ── SUSPENDED USER (demo) ─────────────────────────────────────────────
        $mustaphaUser = User::where('email', 'mustapha@demo.ly')->first();
        if ($mustaphaUser && ! $mustaphaUser->is_suspended) {
            DB::table('users')->where('id', $mustaphaUser->id)->update([
                'is_suspended' => true,
                'suspension_reason' => 'Reported for misleading business information. Under review.',
                'suspended_at' => now()->subDays(2),
                'suspended_by' => $adminId,
            ]);
        }

        // ── SECURITY FLAGGED USER (demo) ──────────────────────────────────────
        $ibrahimUser = User::where('email', 'ibrahim@demo.ly')->first();
        if ($ibrahimUser) {
            DB::table('users')->where('id', $ibrahimUser->id)->update([
                'security_flagged' => true,
                'failed_login_attempts' => 22,
                'last_failed_login_at' => now()->subHours(3),
            ]);
        }

        $this->command->info('Demo data seeded successfully.');
        $this->command->table(
            ['Type', 'Count'],
            [
                ['Providers',     User::role('provider')->count()],
                ['Reviewers',     User::role('user')->count()],
                ['Profiles',      Profile::count()],
                ['Subscriptions', Subscription::count()],
                ['Reviews',       Review::count()],
                ['Pending',       Review::where('status', 'pending')->count()],
                ['Flagged',       Review::where('is_flagged', true)->count()],
                ['Suspended',     User::where('is_suspended', true)->count()],
                ['Sec. Flagged',  User::where('security_flagged', true)->count()],
            ]
        );
    }

    /** @return int[] */
    private function buildRatingDistribution(float $target, int $count): array
    {
        $ratings = [];
        $sum = 0;
        $limit = min($count, 4); // max 4 reviewers

        for ($i = 0; $i < $limit; $i++) {
            if ($i === $limit - 1) {
                $remaining = ($target * $limit) - $sum;
                $rating = (int) round(max(1, min(5, $remaining)));
            } else {
                $rating = (int) round($target + rand(-1, 1));
                $rating = max(1, min(5, $rating));
            }
            $ratings[] = $rating;
            $sum += $rating;
        }

        return $ratings;
    }
}
