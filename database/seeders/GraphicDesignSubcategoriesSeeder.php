<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Subcategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GraphicDesignSubcategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed or find the Graphic Design category
        $category = Category::where('name_ar', 'التصميم الجرافيكي')->first()
            ?? Category::find(1)
            ?? Category::create([
                'name' => 'Graphic Design',
                'name_ar' => 'التصميم الجرافيكي',
                'slug' => 'graphic-design',
                'is_active' => true,
            ]);

        // Just in case it didn't have a clean slug or name_ar
        $category->update([
            'name_ar' => 'التصميم الجرافيكي',
            'is_active' => true,
        ]);

        // 2. Seed 20 subcategories under this category
        $subcategoriesData = [
            ['name' => 'Logo Design', 'name_ar' => 'تصميم الشعارات', 'slug' => 'logo-design'],
            ['name' => 'Brand Identity', 'name_ar' => 'الهوية البصرية', 'slug' => 'brand-identity-graphics'],
            ['name' => 'Social Media Graphics', 'name_ar' => 'تصاميم السوشيال ميديا', 'slug' => 'social-media-graphics'],
            ['name' => 'Business Cards', 'name_ar' => 'بطاقات الأعمال', 'slug' => 'business-cards'],
            ['name' => 'Stationery Design', 'name_ar' => 'القرطاسية والمطبوعات', 'slug' => 'stationery-design'],
            ['name' => 'Brochure & Flyer', 'name_ar' => 'بروشور وفلاير', 'slug' => 'brochure-flyer'],
            ['name' => 'Packaging Design', 'name_ar' => 'تصميم التعبئة والتغليف', 'slug' => 'packaging-design'],
            ['name' => 'Book & Magazine Layout', 'name_ar' => 'تنسيق الكتب والمجلات', 'slug' => 'book-magazine-layout'],
            ['name' => 'Poster Design', 'name_ar' => 'تصميم البوسترات', 'slug' => 'poster-design'],
            ['name' => 'UI/UX Design', 'name_ar' => 'تصميم واجهات المستخدم', 'slug' => 'ui-ux-design-graphics'],
            ['name' => 'Web Design Graphics', 'name_ar' => 'رسومات المواقع الإلكترونية', 'slug' => 'web-design-graphics'],
            ['name' => 'App Design Graphics', 'name_ar' => 'رسومات تطبيقات الهاتف', 'slug' => 'app-design-graphics'],
            ['name' => 'Infographics', 'name_ar' => 'تصميم الإنفوجرافيك', 'slug' => 'infographics'],
            ['name' => 'Vector Illustration', 'name_ar' => 'الرسم الشعاعي (فيكتور)', 'slug' => 'vector-illustration'],
            ['name' => '3D Modeling', 'name_ar' => 'النمذجة ثلاثية الأبعاد', 'slug' => '3d-modeling'],
            ['name' => 'Motion Graphics', 'name_ar' => 'موشن جرافيك', 'slug' => 'motion-graphics-design'],
            ['name' => 'T-shirt & Merchandise', 'name_ar' => 'تصميم الملابس والمنتجات', 'slug' => 't-shirt-merchandise'],
            ['name' => 'Presentation Design', 'name_ar' => 'تصميم العروض التقديمية', 'slug' => 'presentation-design'],
            ['name' => 'Photo Editing', 'name_ar' => 'تعديل الصور الرقمية', 'slug' => 'photo-editing'],
            ['name' => 'Arabic Calligraphy', 'name_ar' => 'الخط العربي والزخرفة', 'slug' => 'arabic-calligraphy-design'],
        ];

        $seededSubcategories = [];
        foreach ($subcategoriesData as $index => $sub) {
            $seededSubcategories[$sub['slug']] = Subcategory::updateOrCreate(
                ['slug' => $sub['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $sub['name'],
                    'name_ar' => $sub['name_ar'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        // 3. Seed 5 Graphic Design mock providers/profiles
        $providers = [
            [
                'user_name' => 'أحمد الهادي الصادق',
                'user_email' => 'ahmad.graphics@delni.ly',
                'business_name' => 'أحمد للصناعة البصرية',
                'bio' => 'مصمم جرافيك مستقل متخصص في بناء وتطوير الهويات البصرية والشعارات وتصاميم السوشيال ميديا المبتكرة.',
                'city_slug' => 'tripoli',
                'subcategories' => ['logo-design', 'brand-identity-graphics', 'social-media-graphics'],
                'experience_years' => 6,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218911234567',
                'phone' => '+218911234567',
                'offers_remote_work' => true,
                'reviews' => [5, 5, 4, 5, 5],
            ],
            [
                'user_name' => 'أسماء الفيتوري',
                'user_email' => 'asma.art@delni.ly',
                'business_name' => 'استوديو فيتوري الإبداعي',
                'bio' => 'استوديو تصميم إبداعي يقدم خدمات تصميم المطبوعات والتغليف الفاخر بالإضافة إلى الرسوم الفنية الرقمية.',
                'city_slug' => 'benghazi',
                'subcategories' => ['brochure-flyer', 'packaging-design', 'vector-illustration'],
                'experience_years' => 8,
                'type' => 'business',
                'provider_type' => 'agency',
                'whatsapp' => '+218921234567',
                'phone' => '+218921234567',
                'offers_remote_work' => true,
                'reviews' => [5, 5, 5, 4],
            ],
            [
                'user_name' => 'سالم الوداني',
                'user_email' => 'salem.motion@delni.ly',
                'business_name' => 'الوداني للموشن جرافيك',
                'bio' => 'خبير إنتاج الموشن جرافيك والتحريك ثلاثي الأبعاد وصناعة الإعلانات المرئية بجودة احترافية.',
                'city_slug' => 'misrata',
                'subcategories' => ['motion-graphics-design', '3d-modeling'],
                'experience_years' => 5,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218931234567',
                'phone' => '+218931234567',
                'offers_remote_work' => false,
                'reviews' => [4, 5, 5, 5, 5, 4],
            ],
            [
                'user_name' => 'رانيا الورفلي',
                'user_email' => 'rania.design@delni.ly',
                'business_name' => 'رانيا للتصميم الإبداعي',
                'bio' => 'مصممة واجهات استخدام ورسومات مواقع إلكترونية وتطبيقات تسعى لتقديم أفضل تجربة بصرية للمستخدم.',
                'city_slug' => 'sabha',
                'subcategories' => ['ui-ux-design-graphics', 'web-design-graphics', 'app-design-graphics'],
                'experience_years' => 4,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218941234567',
                'phone' => '+218941234567',
                'offers_remote_work' => true,
                'reviews' => [5, 4, 5],
            ],
            [
                'user_name' => 'يوسف الشريف',
                'user_email' => 'youssef.print@delni.ly',
                'business_name' => 'الشريف للمطبوعات والبنرات',
                'bio' => 'وكالة طباعة وتصميم متخصصة في تقديم حلول تصميم المطبوعات الورقية، والبوسترات الكبيرة وبطاقات الأعمال الراقية.',
                'city_slug' => 'tripoli',
                'subcategories' => ['poster-design', 'business-cards', 'stationery-design'],
                'experience_years' => 10,
                'type' => 'business',
                'provider_type' => 'agency',
                'whatsapp' => '+218951234567',
                'phone' => '+218951234567',
                'offers_remote_work' => false,
                'reviews' => [5, 5, 5, 5, 5],
            ],
        ];

        foreach ($providers as $provData) {
            $user = User::firstOrCreate(
                ['email' => $provData['user_email']],
                [
                    'name' => $provData['user_name'],
                    'password' => Hash::make('provider123'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'is_suspended' => false,
                ]
            );

            if (! $user->hasRole('provider')) {
                $user->assignRole('provider');
            }

            // Resolve city
            $city = City::where('slug', $provData['city_slug'])->first() ?? City::first() ?? City::create([
                'name' => ucfirst($provData['city_slug']),
                'name_ar' => $provData['city_slug'] === 'tripoli' ? 'طرابلس' : ($provData['city_slug'] === 'benghazi' ? 'بنغازي' : ($provData['city_slug'] === 'misrata' ? 'مصراتة' : 'سبها')),
                'slug' => $provData['city_slug'],
                'is_active' => true,
            ]);

            $slug = Str::slug($provData['business_name']);
            if (Profile::where('slug', $slug)->whereNot('user_id', $user->id)->exists()) {
                $slug .= '-'.$user->id;
            }

            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $provData['business_name'],
                    'bio' => $provData['bio'],
                    'slug' => $slug,
                    'type' => $provData['type'],
                    'provider_type' => $provData['provider_type'],
                    'city_id' => $city->id,
                    'category_id' => $category->id,
                    'whatsapp' => $provData['whatsapp'],
                    'phone' => $provData['phone'],
                    'experience_years' => $provData['experience_years'],
                    'offers_remote_work' => $provData['offers_remote_work'],
                    'is_complete' => true,
                    'provider_access_ends_at' => Carbon::today()->addYear(),
                ]
            );

            // Sync the subcategories
            $subcategoryIds = [];
            foreach ($provData['subcategories'] as $subSlug) {
                if (isset($seededSubcategories[$subSlug])) {
                    $subcategoryIds[] = $seededSubcategories[$subSlug]->id;
                }
            }
            $profile->subcategories()->sync($subcategoryIds);

            // Create stats
            $reviews = $provData['reviews'];
            $count = count($reviews);
            $avg = $count > 0 ? round(array_sum($reviews) / $count, 1) : 0.0;
            $isTopRated = $avg >= 4.5 && $count >= 5;

            ProfileStats::updateOrCreate(
                ['profile_id' => $profile->id],
                [
                    'reviews_count' => $count,
                    'rating_avg' => $avg,
                    'is_top_rated' => $isTopRated,
                    'is_homepage_featured' => $count >= 5, // Mark as featured if it has good ratings
                    'homepage_featured_until' => Carbon::today()->addMonths(2),
                    'is_top_search' => false,
                    'top_search_until' => null,
                    'is_top_category' => false,
                    'top_category_until' => null,
                    'is_top_subcategory' => false,
                    'top_subcategory_until' => null,
                ]
            );
        }
    }
}
