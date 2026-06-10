<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioImage;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\ProviderCredential;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompleteProviderSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create user
        $user = User::firstOrCreate(['email' => 'complete@provider.ly'], [
            'name' => 'محمد الكامل',
            'phone' => '+218913456789',
            'password' => Hash::make('Demo@1234'),
            'is_active' => true,
            'is_suspended' => false,
            'email_verified_at' => now(),
        ]);

        if (! $user->hasRole('provider')) {
            $user->assignRole('provider');
        }

        // Create complete profile with ALL fields
        $slug = Str::slug('استوديو محمد الكامل').'-'.$user->id;
        $profile = Profile::firstOrCreate(['user_id' => $user->id], [
            // Core fields
            'business_name' => 'استوديو محمد الكامل للتصميم والتصوير',
            'type' => 'business',
            'provider_type' => 'design',
            'bio' => 'استوديو احترافي متخصص في التصميم الجرافيكي والتصوير الفوتوغرافي والفيديو. لدينا خبرة تزيد عن 15 سنة في هذا المجال. نقدم خدمات متكاملة من التصميم والتصوير إلى المونتاج والإنتاج الكامل. نعمل مع العلامات التجارية الكبرى والشركات الناشئة.',
            'slug' => $slug,

            // Contact
            'phone' => '+218913456789',
            'whatsapp' => '+218913456789',

            // Social & Web
            'website' => 'https://mohammedkamil.design',
            'instagram' => 'https://instagram.com/mohammedkamil.design',
            'facebook' => 'https://facebook.com/mohammedkamildesign',
            'linkedin' => 'https://linkedin.com/in/mohammedkamil',

            // Experience
            'experience_years' => 15,

            // Location & Service
            'offers_remote_work' => true,
            'map_url' => 'https://maps.google.com/?q=tripoli+libya',
            'service_area_note' => 'نخدم جميع مناطق ليبيا. نوفر خدمات التصميم والتصوير للشركات والعلامات التجارية والأفراد. متخصصون في الهوية البصرية والبروشورات والملصقات والصور الاحترافية.',

            // Status
            'city_id' => City::where('slug', 'tripoli')->value('id'),
            'category_id' => Category::where('name', 'Graphic Design')->value('id'),
            'is_complete' => true,
        ]);

        // Add subcategories
        $subcategoryIds = $profile->category?->subcategories()->pluck('id')->take(5)->toArray() ?? [];
        if (! empty($subcategoryIds)) {
            $profile->subcategories()->sync($subcategoryIds);
        }

        // Create active subscription so profile is visible
        $plan = SubscriptionPlan::first();
        if ($plan) {
            Subscription::firstOrCreate(
                ['user_id' => $user->id, 'plan_id' => $plan->id],
                [
                    'is_active' => true,
                    'starts_at' => now()->toDateString(),
                    'ends_at' => now()->addYear()->toDateString(),
                ]
            );
        }

        // Create ProfileStats
        ProfileStats::updateOrCreate(['profile_id' => $profile->id], [
            'rating_avg' => 4.8,
            'reviews_count' => 12,
            'is_top_rated' => true,
            'is_homepage_featured' => true,
            'homepage_featured_until' => Carbon::tomorrow()->addMonths(3),
            'is_top_search' => true,
            'top_search_until' => Carbon::tomorrow()->addMonths(2),
            'is_top_category' => true,
            'top_category_until' => Carbon::tomorrow()->addMonths(3),
            'is_featured' => true,
            'featured_until' => Carbon::tomorrow()->addMonths(1),
        ]);

        // Create Portfolio Item 1
        $project1 = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'تصميم هوية بصرية لشركة التقنية الحديثة',
            'short_description' => 'تصميم شامل للهوية البصرية يشمل اللوجو والألوان والخطوط.',
            'description' => 'قمنا بتصميم هوية بصرية متكاملة لشركة التقنية الحديثة. شملت المشروع تصميم اللوجو المميز واختيار نظام الألوان الاحترافي والخطوط المناسبة. تم تطبيق الهوية على جميع المواد التسويقية والرسمية للشركة. النتيجة كانت هوية عصرية وقوية تعكس قيم الشركة والتزامها بالتطور التكنولوجي.',
            'main_url' => 'https://mohammedkamil.design/project-1',
            'link' => null,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Add images to project 1
        $images1 = [
            ['title' => 'اللوجو الرئيسي'],
            ['title' => 'نظام الألوان'],
            ['title' => 'التطبيقات على المواد'],
            ['title' => 'الدليل التصميمي'],
        ];
        foreach ($images1 as $index => $img) {
            PortfolioImage::create([
                'portfolio_item_id' => $project1->id,
                'path' => 'portfolio/images/'.Str::uuid().'.webp',
                'alt' => $img['title'],
                'sort_order' => $index + 1,
            ]);
        }

        // Create Portfolio Item 2
        $project2 = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'جلسة تصوير فوتوغرافية احترافية لفندق 5 نجوم',
            'short_description' => 'تصوير احترافي عالي الجودة لمرافق الفندق والغرف والمطاعم.',
            'description' => 'قمنا بجلسة تصوير شاملة لفندق 5 نجوم في طرابلس. صورنا جميع مرافق الفندق بما فيها الغرف والأجنحة والمطاعم والمسابح والقاعات. استخدمنا أحدث المعدات والإضاءة الاحترافية لإظهار جمال الفندق وفخامته. تم إنتاج أكثر من 500 صورة احترافية جاهزة للنشر على المواقع والوسائط الاجتماعية.',
            'main_url' => 'https://mohammedkamil.design/project-2',
            'link' => null,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Add images to project 2
        $images2 = [
            ['title' => 'منظر الفندق الخارجي'],
            ['title' => 'غرفة ديلوكس'],
            ['title' => 'المطعم الرئيسي'],
            ['title' => 'حمام السباحة'],
        ];
        foreach ($images2 as $index => $img) {
            PortfolioImage::create([
                'portfolio_item_id' => $project2->id,
                'path' => 'portfolio/images/'.Str::uuid().'.webp',
                'alt' => $img['title'],
                'sort_order' => $index + 1,
            ]);
        }

        // Create Credentials/Certificates
        $credentials = [
            [
                'title' => 'Adobe Certified Associate - Graphic Design',
                'issuer' => 'Adobe',
                'verification_url' => 'https://adobe.com/verify',
                'issue_date' => Carbon::now()->subYears(5),
                'notes' => 'شهادة معتمدة من أدوبي في التصميم الجرافيكي. تثبت الكفاءة في استخدام برامج Adobe Creative Suite.',
            ],
            [
                'title' => 'Professional Photography Certificate',
                'issuer' => 'International Association of Professional Photographers',
                'verification_url' => 'https://iapp.org/verify',
                'issue_date' => Carbon::now()->subYears(6),
                'notes' => 'شهادة احترافية في التصوير الفوتوغرافي من الجمعية الدولية للمصورين المحترفين.',
            ],
            [
                'title' => 'Digital Marketing & Branding Specialist',
                'issuer' => 'Google Digital Academy',
                'verification_url' => 'https://google-academy.org/verify',
                'issue_date' => Carbon::now()->subYears(3),
                'notes' => 'دورة متقدمة في التسويق الرقمي والهوية البصرية من أكاديمية جوجل للتسويق الرقمي.',
            ],
            [
                'title' => 'Video Production & Editing Masterclass',
                'issuer' => 'Skillshare Premium',
                'verification_url' => 'https://skillshare.com/verify',
                'issue_date' => Carbon::now()->subYears(2),
                'notes' => 'دبلوم متخصص في إنتاج الفيديو والمونتاج الاحترافي.',
            ],
            [
                'title' => 'Business License - Design & Photography',
                'issuer' => 'وزارة التجارة والصناعة - ليبيا',
                'verification_url' => null,
                'issue_date' => Carbon::now()->subYears(10),
                'notes' => 'رخصة رسمية لمزاولة مهنة التصميم والتصوير الفوتوغرافي من الجهات الرسمية الليبية.',
            ],
        ];

        foreach ($credentials as $cred) {
            ProviderCredential::create([
                'profile_id' => $profile->id,
                ...$cred,
            ]);
        }

        // Create reviews
        $reviewers = User::role('user')->inRandomOrder()->take(5)->get();
        $reviews = [
            ['rating' => 5, 'comment' => 'عمل رائع جداً! استوديو احترافي جداً. تصاميمهم خلاقة وفريدة. سأتعامل معهم مرة أخرى.'],
            ['rating' => 5, 'comment' => 'أفضل مصور قابلته. صور احترافية وسعر عادل. أنصح به بشدة.'],
            ['rating' => 4, 'comment' => 'تعامل احترافي وعمل متقن. التواصل سريع والنتائج ممتازة. يمكن تحسين وقت التسليم فقط.'],
            ['rating' => 5, 'comment' => 'تصميمهم انعكس بشكل مباشر على مبيعاتنا! فريق موهوب ومحترف.'],
            ['rating' => 5, 'comment' => 'جودة عالية جداً وتفانٍ في العمل. يستحقون التقدير والتوصية.'],
        ];

        foreach ($reviews as $index => $reviewData) {
            if ($index < count($reviewers)) {
                Review::firstOrCreate(
                    [
                        'profile_id' => $profile->id,
                        'user_id' => $reviewers[$index]->id,
                    ],
                    [
                        'rating' => $reviewData['rating'],
                        'comment' => $reviewData['comment'],
                        'status' => 'approved',
                        'moderated_by' => User::where('email', env('SUPER_ADMIN_EMAIL'))->value('id'),
                        'moderated_at' => now(),
                        'created_at' => now()->subDays(rand(5, 30)),
                        'updated_at' => now()->subDays(rand(1, 5)),
                    ]
                );
            }
        }

        $this->command->info('✅ Complete provider seeded successfully!');
        $this->command->table(
            ['📊 Field', 'Value'],
            [
                ['Provider Name', 'محمد الكامل'],
                ['Email', 'complete@provider.ly'],
                ['Category', 'Graphic Design'],
                ['City', 'Tripoli'],
                ['Experience', '15 years'],
                ['Portfolio Projects', '2'],
                ['Project Images', '8 (4 per project)'],
                ['Certificates', '5'],
                ['Reviews', '5'],
                ['Rating', '4.8 ⭐'],
                ['Featured', 'Homepage Featured ✓'],
                ['Remote Work', 'Yes ✓'],
            ]
        );

        $this->command->newLine();
        $this->command->info('🔗 View profile at:');
        $this->command->line("  • http://localhost:8080/providers/{$profile->slug}");
    }
}
