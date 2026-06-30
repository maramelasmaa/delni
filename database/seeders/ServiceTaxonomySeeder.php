<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Icon;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the Arabic service discovery taxonomy used by the mobile app.
 *
 * Idempotent: safe to run repeatedly in production. Existing rows are matched by
 * slug and updated without deleting categories, subcategories, or profile pivots.
 */
class ServiceTaxonomySeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     slug: string,
     *     name: string,
     *     name_ar: string,
     *     icon: string,
     *     fallback_icon: string,
     *     subcategories: array<int, array{name: string, name_ar: string, slug: string}>
     * }>
     */
    private array $categories = [
        [
            'slug' => 'health',
            'name' => 'Health',
            'name_ar' => 'الصحة',
            'icon' => 'hospital',
            'fallback_icon' => 'cat-health',
            'subcategories' => [
                ['name' => 'Medical Centers', 'name_ar' => 'مصحات', 'slug' => 'medical-centers'],
                ['name' => 'Clinics', 'name_ar' => 'عيادات', 'slug' => 'clinics'],
                ['name' => 'Dental Clinics', 'name_ar' => 'عيادات أسنان', 'slug' => 'dental-clinics'],
                ['name' => 'Laboratories', 'name_ar' => 'مختبرات', 'slug' => 'laboratories'],
                ['name' => 'Radiology Centers', 'name_ar' => 'مراكز أشعة', 'slug' => 'radiology-centers'],
                ['name' => 'Pharmacies', 'name_ar' => 'صيدليات', 'slug' => 'pharmacies'],
            ],
        ],
        [
            'slug' => 'restaurants-cafes',
            'name' => 'Restaurants and Cafes',
            'name_ar' => 'المطاعم والمقاهي',
            'icon' => 'tools-kitchen-2',
            'fallback_icon' => 'cat-food',
            'subcategories' => [
                ['name' => 'Restaurants', 'name_ar' => 'مطاعم', 'slug' => 'restaurants'],
                ['name' => 'Cafes', 'name_ar' => 'كافيهات', 'slug' => 'cafes'],
                ['name' => 'Coffee Shops', 'name_ar' => 'مقاهي', 'slug' => 'coffee-shops'],
                ['name' => 'Bakeries', 'name_ar' => 'مخابز', 'slug' => 'bakeries'],
                ['name' => 'Sweets', 'name_ar' => 'حلويات', 'slug' => 'sweets'],
                ['name' => 'Event Catering', 'name_ar' => 'تموين حفلات', 'slug' => 'event-catering'],
            ],
        ],
        [
            'slug' => 'shopping',
            'name' => 'Shopping',
            'name_ar' => 'التسوق',
            'icon' => 'shopping-bag',
            'fallback_icon' => 'cat-marketing',
            'subcategories' => [
                ['name' => 'Supermarkets', 'name_ar' => 'سوبر ماركت', 'slug' => 'supermarkets'],
                ['name' => 'Clothing', 'name_ar' => 'ملابس', 'slug' => 'clothing'],
                ['name' => 'Shoes', 'name_ar' => 'أحذية', 'slug' => 'shoes'],
                ['name' => 'Perfumes', 'name_ar' => 'عطور', 'slug' => 'perfumes'],
                ['name' => 'Electronics', 'name_ar' => 'إلكترونيات', 'slug' => 'electronics'],
                ['name' => 'Furniture', 'name_ar' => 'أثاث', 'slug' => 'furniture'],
                ['name' => 'Gifts', 'name_ar' => 'هدايا', 'slug' => 'gifts'],
            ],
        ],
        [
            'slug' => 'cars',
            'name' => 'Cars',
            'name_ar' => 'السيارات',
            'icon' => 'car',
            'fallback_icon' => 'cat-transport',
            'subcategories' => [
                ['name' => 'Car Dealerships', 'name_ar' => 'معارض سيارات', 'slug' => 'car-dealerships'],
                ['name' => 'Mechanic Workshops', 'name_ar' => 'ورش ميكانيكا', 'slug' => 'mechanic-workshops'],
                ['name' => 'Auto Electricians', 'name_ar' => 'كهرباء سيارات', 'slug' => 'auto-electricians'],
                ['name' => 'Car Wash', 'name_ar' => 'غسيل سيارات', 'slug' => 'car-wash'],
                ['name' => 'Tires', 'name_ar' => 'إطارات', 'slug' => 'tires'],
                ['name' => 'Batteries', 'name_ar' => 'بطاريات', 'slug' => 'batteries'],
                ['name' => 'Spare Parts', 'name_ar' => 'قطع غيار', 'slug' => 'spare-parts'],
            ],
        ],
        [
            'slug' => 'home-maintenance',
            'name' => 'Home and Maintenance',
            'name_ar' => 'المنزل والصيانة',
            'icon' => 'home-cog',
            'fallback_icon' => 'cat-home',
            'subcategories' => [
                ['name' => 'Plumbing', 'name_ar' => 'سباكة', 'slug' => 'plumbing'],
                ['name' => 'Electricity', 'name_ar' => 'كهرباء', 'slug' => 'electricity'],
                ['name' => 'Air Conditioning', 'name_ar' => 'تكييف', 'slug' => 'air-conditioning'],
                ['name' => 'Carpentry', 'name_ar' => 'نجارة', 'slug' => 'carpentry'],
                ['name' => 'Painting', 'name_ar' => 'دهان', 'slug' => 'painting'],
                ['name' => 'Cleaning', 'name_ar' => 'تنظيف', 'slug' => 'cleaning'],
                ['name' => 'Pest Control', 'name_ar' => 'مكافحة حشرات', 'slug' => 'pest-control'],
            ],
        ],
        [
            'slug' => 'construction-engineering',
            'name' => 'Construction and Engineering',
            'name_ar' => 'البناء والهندسة',
            'icon' => 'building-skyscraper',
            'fallback_icon' => 'cat-home',
            'subcategories' => [
                ['name' => 'Contracting Companies', 'name_ar' => 'شركات مقاولات', 'slug' => 'contracting-companies'],
                ['name' => 'Engineering Offices', 'name_ar' => 'مكاتب هندسية', 'slug' => 'engineering-offices'],
                ['name' => 'Interior Design', 'name_ar' => 'تصميم داخلي', 'slug' => 'interior-design'],
                ['name' => 'Building Materials', 'name_ar' => 'مواد بناء', 'slug' => 'building-materials'],
                ['name' => 'Aluminum', 'name_ar' => 'ألمنيوم', 'slug' => 'aluminum'],
                ['name' => 'Marble and Granite', 'name_ar' => 'رخام وجرانيت', 'slug' => 'marble-granite'],
            ],
        ],
        [
            'slug' => 'technology',
            'name' => 'Technology',
            'name_ar' => 'التقنية',
            'icon' => 'device-laptop',
            'fallback_icon' => 'cat-tech',
            'subcategories' => [
                ['name' => 'Software Companies', 'name_ar' => 'شركات برمجيات', 'slug' => 'software-companies'],
                ['name' => 'Website Design', 'name_ar' => 'تصميم مواقع', 'slug' => 'website-design'],
                ['name' => 'App Design', 'name_ar' => 'تصميم تطبيقات', 'slug' => 'app-design'],
                ['name' => 'Computer Maintenance', 'name_ar' => 'صيانة كمبيوتر', 'slug' => 'computer-maintenance'],
                ['name' => 'Phone Maintenance', 'name_ar' => 'صيانة هواتف', 'slug' => 'phone-maintenance'],
                ['name' => 'Surveillance Cameras', 'name_ar' => 'كاميرات مراقبة', 'slug' => 'surveillance-cameras'],
            ],
        ],
        [
            'slug' => 'business-services',
            'name' => 'Business and Services',
            'name_ar' => 'الشركات والخدمات',
            'icon' => 'building-store',
            'fallback_icon' => 'cat-marketing',
            'subcategories' => [
                ['name' => 'Shipping Companies', 'name_ar' => 'شركات شحن', 'slug' => 'shipping-companies'],
                ['name' => 'Cleaning Companies', 'name_ar' => 'شركات نظافة', 'slug' => 'cleaning-companies'],
                ['name' => 'Marketing Companies', 'name_ar' => 'شركات تسويق', 'slug' => 'marketing-companies'],
                ['name' => 'Advertising', 'name_ar' => 'دعاية وإعلان', 'slug' => 'advertising'],
                ['name' => 'Import and Export', 'name_ar' => 'استيراد وتصدير', 'slug' => 'import-export'],
            ],
        ],
        [
            'slug' => 'education',
            'name' => 'Education',
            'name_ar' => 'التعليم',
            'icon' => 'school',
            'fallback_icon' => 'cat-education',
            'subcategories' => [
                ['name' => 'Schools', 'name_ar' => 'مدارس', 'slug' => 'schools'],
                ['name' => 'Nurseries', 'name_ar' => 'حضانات', 'slug' => 'nurseries'],
                ['name' => 'Universities', 'name_ar' => 'جامعات', 'slug' => 'universities'],
                ['name' => 'Institutes', 'name_ar' => 'معاهد', 'slug' => 'institutes'],
                ['name' => 'Training Centers', 'name_ar' => 'مراكز تدريب', 'slug' => 'training-centers'],
                ['name' => 'Language Centers', 'name_ar' => 'مراكز لغات', 'slug' => 'language-centers'],
                ['name' => 'Driving Schools', 'name_ar' => 'مدارس قيادة', 'slug' => 'driving-schools'],
            ],
        ],
        [
            'slug' => 'real-estate',
            'name' => 'Real Estate',
            'name_ar' => 'العقارات',
            'icon' => 'home-dollar',
            'fallback_icon' => 'cat-home',
            'subcategories' => [
                ['name' => 'Real Estate Offices', 'name_ar' => 'مكاتب عقارية', 'slug' => 'real-estate-offices'],
                ['name' => 'Buying and Selling', 'name_ar' => 'بيع وشراء', 'slug' => 'buying-selling'],
                ['name' => 'Rentals', 'name_ar' => 'إيجار', 'slug' => 'rentals'],
                ['name' => 'Property Management', 'name_ar' => 'إدارة أملاك', 'slug' => 'property-management'],
                ['name' => 'Real Estate Marketing', 'name_ar' => 'تسويق عقاري', 'slug' => 'real-estate-marketing'],
            ],
        ],
        [
            'slug' => 'beauty-care',
            'name' => 'Beauty and Care',
            'name_ar' => 'الجمال والعناية',
            'icon' => 'sparkles',
            'fallback_icon' => 'cat-design',
            'subcategories' => [
                ['name' => 'Women Salons', 'name_ar' => 'صالونات نسائية', 'slug' => 'women-salons'],
                ['name' => 'Men Salons', 'name_ar' => 'صالونات رجالية', 'slug' => 'men-salons'],
                ['name' => 'Beauty Centers', 'name_ar' => 'مراكز تجميل', 'slug' => 'beauty-centers'],
                ['name' => 'Laser', 'name_ar' => 'ليزر', 'slug' => 'laser'],
                ['name' => 'Skin Care', 'name_ar' => 'عناية بالبشرة', 'slug' => 'skin-care'],
                ['name' => 'Spa', 'name_ar' => 'سبا', 'slug' => 'spa'],
            ],
        ],
        [
            'slug' => 'events',
            'name' => 'Events',
            'name_ar' => 'المناسبات',
            'icon' => 'confetti',
            'fallback_icon' => 'cat-photography',
            'subcategories' => [
                ['name' => 'Wedding Halls', 'name_ar' => 'قاعات أفراح', 'slug' => 'wedding-halls'],
                ['name' => 'Photography', 'name_ar' => 'تصوير', 'slug' => 'photography'],
                ['name' => 'Videography', 'name_ar' => 'تصوير فيديو', 'slug' => 'videography'],
                ['name' => 'Event Planning', 'name_ar' => 'تنظيم حفلات', 'slug' => 'event-planning'],
                ['name' => 'Event Decoration', 'name_ar' => 'ديكور مناسبات', 'slug' => 'event-decoration'],
                ['name' => 'Gifts and Wrapping', 'name_ar' => 'هدايا وتغليف', 'slug' => 'gifts-wrapping'],
            ],
        ],
    ];

    public function run(): void
    {
        $icons = Icon::query()->pluck('id', 'slug');

        foreach ($this->categories as $categoryIndex => $categoryData) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'name_ar' => $categoryData['name_ar'],
                    'icon_id' => $this->iconId($icons, $categoryData['icon'], $categoryData['fallback_icon']),
                    'sort_order' => ($categoryIndex + 1) * 10,
                    'is_active' => true,
                ],
            );

            foreach ($categoryData['subcategories'] as $subcategoryIndex => $subcategoryData) {
                Subcategory::query()->updateOrCreate(
                    ['slug' => $subcategoryData['slug']],
                    [
                        'category_id' => $category->id,
                        'name' => $subcategoryData['name'],
                        'name_ar' => $subcategoryData['name_ar'],
                        'search_name' => $this->searchName($subcategoryData['name_ar']),
                        'icon_id' => $category->icon_id,
                        'sort_order' => ($subcategoryIndex + 1) * 10,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    /**
     * @param \Illuminate\Support\Collection<string, int> $icons
     */
    private function iconId($icons, string $preferredSlug, string $fallbackSlug): ?int
    {
        return $icons->get($preferredSlug) ?? $icons->get($fallbackSlug);
    }

    private function searchName(string $value): string
    {
        return Str::of($value)
            ->replace(['أ', 'إ', 'آ'], 'ا')
            ->replace('ى', 'ي')
            ->replace('ة', 'ه')
            ->replaceMatches('/[^\p{Arabic}\p{L}\p{N}\s]+/u', ' ')
            ->squish()
            ->lower()
            ->value();
    }
}
