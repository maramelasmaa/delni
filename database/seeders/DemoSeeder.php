<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Icon;
use App\Models\PortfolioImage;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->findAdmin();
        $icons = $this->seedIcons($admin);
        $cities = $this->seedCities();
        $categories = $this->seedCategories($icons);
        $reviewers = $this->seedReviewers();
        $this->seedProviders($cities, $categories, $reviewers);
    }

    private function findAdmin(): User
    {
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first()
            ?? User::first();

        if (! $admin) {
            $admin = User::firstOrCreate(
                ['email' => 'demo-admin@delni.ly'],
                [
                    'name' => 'Demo Admin',
                    'password' => Hash::make('admin123'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'is_suspended' => false,
                ],
            );
        }

        return $admin;
    }

    /** @return array<string, Icon> */
    private function seedIcons(User $admin): array
    {
        $iconDefs = [
            'cat-design' => [
                'name' => 'Category: Design & Creative',
                'color' => '#7C3AED',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>',
            ],
            'cat-tech' => [
                'name' => 'Category: Tech & Programming',
                'color' => '#0EA5E9',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
            ],
            'cat-marketing' => [
                'name' => 'Category: Digital Marketing',
                'color' => '#F59E0B',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
            ],
            'cat-photography' => [
                'name' => 'Category: Photography & Media',
                'color' => '#EC4899',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>',
            ],
            'cat-health' => [
                'name' => 'Category: Health & Beauty',
                'color' => '#10B981',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
            ],
            'cat-home' => [
                'name' => 'Category: Home & Construction',
                'color' => '#F1620F',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
            ],
            'cat-education' => [
                'name' => 'Category: Education & Training',
                'color' => '#6366F1',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
            ],
            'cat-legal' => [
                'name' => 'Category: Legal & Financial',
                'color' => '#64748B',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
            ],
            'cat-transport' => [
                'name' => 'Category: Transport & Logistics',
                'color' => '#0891B2',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
            ],
            'cat-food' => [
                'name' => 'Category: Food & Hospitality',
                'color' => '#DC2626',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>',
            ],
        ];

        Storage::disk('icons')->makeDirectory('');

        $result = [];
        foreach ($iconDefs as $slug => $def) {
            $fileName = "{$slug}.svg";
            Storage::disk('icons')->put($fileName, $def['svg']);

            $icon = Icon::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $def['name'],
                    'file_path' => $fileName,
                    'format' => 'svg',
                    'color' => $def['color'],
                    'uploaded_by' => $admin->id,
                ],
            );

            $result[$slug] = $icon;
        }

        return $result;
    }

    /** @return array<string, City> */
    private function seedCities(): array
    {
        $data = [
            ['name' => 'Tripoli', 'name_ar' => 'طرابلس', 'slug' => 'tripoli', 'sort_order' => 1],
            ['name' => 'Benghazi', 'name_ar' => 'بنغازي', 'slug' => 'benghazi', 'sort_order' => 2],
            ['name' => 'Misrata', 'name_ar' => 'مصراتة', 'slug' => 'misrata', 'sort_order' => 3],
            ['name' => 'Sabha', 'name_ar' => 'سبها', 'slug' => 'sabha', 'sort_order' => 4],
            ['name' => 'Al Bayda', 'name_ar' => 'البيضاء', 'slug' => 'al-bayda', 'sort_order' => 5],
            ['name' => 'Zawiya', 'name_ar' => 'الزاوية', 'slug' => 'zawiya', 'sort_order' => 6],
            ['name' => 'Zliten', 'name_ar' => 'زليتن', 'slug' => 'zliten', 'sort_order' => 7],
            ['name' => 'Tobruk', 'name_ar' => 'طبرق', 'slug' => 'tobruk', 'sort_order' => 8],
            ['name' => 'Gharyan', 'name_ar' => 'غريان', 'slug' => 'gharyan', 'sort_order' => 9],
            ['name' => 'Derna', 'name_ar' => 'درنة', 'slug' => 'derna', 'sort_order' => 10],
        ];

        $result = [];
        foreach ($data as $row) {
            $city = City::updateOrCreate(['slug' => $row['slug']], $row + ['is_active' => true]);
            $result[$row['slug']] = $city;
        }

        return $result;
    }

    /**
     * @param  array<string, Icon>  $icons
     * @return array<string, array{category: Category, subcategories: array<string, Subcategory>}>
     */
    private function seedCategories(array $icons): array
    {
        $data = [
            [
                'name' => 'Design & Creative',
                'name_ar' => 'تصميم وإبداع',
                'slug' => 'design-creative',
                'icon' => 'app-edit',
                'icon_id' => $icons['cat-design']->id,
                'sort_order' => 1,
                'subcategories' => [
                    ['name' => 'Graphic Design', 'name_ar' => 'تصميم جرافيك', 'slug' => 'graphic-design', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Web Design', 'name_ar' => 'تصميم مواقع', 'slug' => 'web-design', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Brand Identity', 'name_ar' => 'هوية بصرية', 'slug' => 'brand-identity', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Video & Motion', 'name_ar' => 'فيديو وموشن جرافيك', 'slug' => 'video-motion', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'UI/UX Design', 'name_ar' => 'تصميم واجهات المستخدم', 'slug' => 'ui-ux-design', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Tech & Programming',
                'name_ar' => 'برمجة وتقنية',
                'slug' => 'tech-programming',
                'icon' => 'app-stack',
                'icon_id' => $icons['cat-tech']->id,
                'sort_order' => 2,
                'subcategories' => [
                    ['name' => 'Web Development', 'name_ar' => 'تطوير مواقع', 'slug' => 'web-development', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'App Development', 'name_ar' => 'تطوير تطبيقات', 'slug' => 'app-development', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Backend Development', 'name_ar' => 'برمجة خلفية', 'slug' => 'backend-development', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Cybersecurity', 'name_ar' => 'أمن المعلومات', 'slug' => 'cybersecurity', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Data Analytics', 'name_ar' => 'تحليل البيانات', 'slug' => 'data-analytics', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Digital Marketing',
                'name_ar' => 'تسويق رقمي',
                'slug' => 'digital-marketing',
                'icon' => 'app-users',
                'icon_id' => $icons['cat-marketing']->id,
                'sort_order' => 3,
                'subcategories' => [
                    ['name' => 'Social Media Management', 'name_ar' => 'إدارة سوشيال ميديا', 'slug' => 'social-media-management', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Paid Advertising', 'name_ar' => 'إعلانات مدفوعة', 'slug' => 'paid-ads', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'SEO', 'name_ar' => 'تحسين محركات البحث', 'slug' => 'seo', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Content Writing', 'name_ar' => 'كتابة محتوى', 'slug' => 'content-writing', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Email Marketing', 'name_ar' => 'تسويق بالبريد الإلكتروني', 'slug' => 'email-marketing', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Photography & Media',
                'name_ar' => 'تصوير وإعلام',
                'slug' => 'photography-media',
                'icon' => null,
                'icon_id' => $icons['cat-photography']->id,
                'sort_order' => 4,
                'subcategories' => [
                    ['name' => 'Photography', 'name_ar' => 'تصوير فوتوغرافي', 'slug' => 'photography', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Videography', 'name_ar' => 'تصوير فيديو', 'slug' => 'videography', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Drone Footage', 'name_ar' => 'تصوير جوي بالدرون', 'slug' => 'drone-footage', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Video Editing', 'name_ar' => 'مونتاج وتحرير فيديو', 'slug' => 'video-editing', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Podcast Production', 'name_ar' => 'إنتاج بودكاست', 'slug' => 'podcast-production', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Health & Beauty',
                'name_ar' => 'صحة وجمال',
                'slug' => 'health-beauty',
                'icon' => null,
                'icon_id' => $icons['cat-health']->id,
                'sort_order' => 5,
                'subcategories' => [
                    ['name' => 'Hair Styling', 'name_ar' => 'تصفيف شعر', 'slug' => 'hair-styling', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Makeup Artistry', 'name_ar' => 'مكياج احترافي', 'slug' => 'makeup-artistry', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Skincare', 'name_ar' => 'عناية بالبشرة', 'slug' => 'skincare', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Fitness Coaching', 'name_ar' => 'تدريب رياضي', 'slug' => 'fitness-coaching', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Nutrition Consulting', 'name_ar' => 'استشارات تغذية', 'slug' => 'nutrition-consulting', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Home & Construction',
                'name_ar' => 'خدمات المنزل والبناء',
                'slug' => 'home-services',
                'icon' => 'app-home',
                'icon_id' => $icons['cat-home']->id,
                'sort_order' => 6,
                'subcategories' => [
                    ['name' => 'Plumbing', 'name_ar' => 'سباكة', 'slug' => 'plumbing', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Electrical', 'name_ar' => 'كهرباء', 'slug' => 'electrical', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Carpentry', 'name_ar' => 'نجارة وأبواب', 'slug' => 'carpentry', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'AC & Cooling', 'name_ar' => 'تكييف وتبريد', 'slug' => 'hvac', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Interior Design & Décor', 'name_ar' => 'تصميم داخلي وديكور', 'slug' => 'interior-design', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Education & Training',
                'name_ar' => 'تعليم وتدريب',
                'slug' => 'education-training',
                'icon' => 'app-document',
                'icon_id' => $icons['cat-education']->id,
                'sort_order' => 7,
                'subcategories' => [
                    ['name' => 'Private Tutoring', 'name_ar' => 'دروس خصوصية', 'slug' => 'private-tutoring', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Language Courses', 'name_ar' => 'دورات لغات', 'slug' => 'language-courses', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Vocational Training', 'name_ar' => 'تدريب مهني', 'slug' => 'vocational-training', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Online Courses', 'name_ar' => 'دورات أونلاين', 'slug' => 'online-courses', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Educational Guidance', 'name_ar' => 'إرشاد تعليمي', 'slug' => 'educational-guidance', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Legal & Financial',
                'name_ar' => 'قانوني ومالي',
                'slug' => 'legal-accounting',
                'icon' => 'app-shield',
                'icon_id' => $icons['cat-legal']->id,
                'sort_order' => 8,
                'subcategories' => [
                    ['name' => 'Legal Services', 'name_ar' => 'محاماة واستشارات قانونية', 'slug' => 'legal-services', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Accounting', 'name_ar' => 'محاسبة', 'slug' => 'accounting', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Tax Consulting', 'name_ar' => 'استشارات ضريبية', 'slug' => 'tax-consulting', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Contract Documentation', 'name_ar' => 'توثيق عقود', 'slug' => 'contracts', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Financial Planning', 'name_ar' => 'تخطيط مالي', 'slug' => 'financial-planning', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Transport & Logistics',
                'name_ar' => 'نقل ولوجستيك',
                'slug' => 'transport-logistics',
                'icon' => null,
                'icon_id' => $icons['cat-transport']->id,
                'sort_order' => 9,
                'subcategories' => [
                    ['name' => 'Freight Shipping', 'name_ar' => 'شحن بضائع', 'slug' => 'freight-shipping', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Passenger Transport', 'name_ar' => 'نقل ركاب', 'slug' => 'passenger-transport', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Cargo & Customs', 'name_ar' => 'شحن جمركي', 'slug' => 'cargo-customs', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Moving Services', 'name_ar' => 'نقل أثاث ومنازل', 'slug' => 'moving-services', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Courier & Delivery', 'name_ar' => 'توصيل طرود وبريد', 'slug' => 'courier-delivery', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
            [
                'name' => 'Food & Hospitality',
                'name_ar' => 'غذاء وضيافة',
                'slug' => 'food-hospitality',
                'icon' => null,
                'icon_id' => $icons['cat-food']->id,
                'sort_order' => 10,
                'subcategories' => [
                    ['name' => 'Restaurants & Catering', 'name_ar' => 'مطاعم وضيافة', 'slug' => 'restaurant-catering', 'sort_order' => 1, 'icon_id' => null],
                    ['name' => 'Pastry & Bakery', 'name_ar' => 'حلويات ومخابز', 'slug' => 'pastry-bakery', 'sort_order' => 2, 'icon_id' => null],
                    ['name' => 'Private Chef', 'name_ar' => 'طباخ خاص', 'slug' => 'private-chef', 'sort_order' => 3, 'icon_id' => null],
                    ['name' => 'Event Catering', 'name_ar' => 'تقديم طعام للمناسبات', 'slug' => 'event-catering', 'sort_order' => 4, 'icon_id' => null],
                    ['name' => 'Food Consulting', 'name_ar' => 'استشارات غذائية', 'slug' => 'food-consulting', 'sort_order' => 5, 'icon_id' => null],
                ],
            ],
        ];

        $result = [];
        foreach ($data as $catData) {
            $subcategoryData = $catData['subcategories'];
            unset($catData['subcategories']);

            $category = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                $catData + ['is_active' => true],
            );

            $subcategories = [];
            foreach ($subcategoryData as $subData) {
                $sub = Subcategory::updateOrCreate(
                    ['slug' => $subData['slug']],
                    $subData + ['category_id' => $category->id, 'is_active' => true],
                );
                $subcategories[$subData['slug']] = $sub;
            }

            $result[$catData['slug']] = ['category' => $category, 'subcategories' => $subcategories];
        }

        return $result;
    }

    /** @return array<int, User> */
    private function seedReviewers(): array
    {
        $reviewerData = [
            ['name' => 'يوسف المريمي', 'email' => 'youssef.demo@delni.ly'],
            ['name' => 'حنان الرقيعي', 'email' => 'hanan.demo@delni.ly'],
            ['name' => 'طارق الزواوي', 'email' => 'tarek.demo@delni.ly'],
            ['name' => 'منى الشارف', 'email' => 'mona.demo@delni.ly'],
            ['name' => 'إبراهيم الأمين', 'email' => 'ibrahim.demo@delni.ly'],
            ['name' => 'نوال الفارسي', 'email' => 'nawal.demo@delni.ly'],
            ['name' => 'رامي المبروك', 'email' => 'rami.demo@delni.ly'],
            ['name' => 'سلمى البرعصي', 'email' => 'salma.demo@delni.ly'],
            ['name' => 'محمد الطاهر', 'email' => 'mohammedtaher.demo@delni.ly'],
            ['name' => 'زينب القاضي', 'email' => 'zainab.demo@delni.ly'],
        ];

        $reviewers = [];
        foreach ($reviewerData as $data) {
            $reviewers[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'is_suspended' => false,
                ],
            );
        }

        return $reviewers;
    }

    /**
     * @param  array<string, City>  $cities
     * @param  array<string, array{category: Category, subcategories: array<string, Subcategory>}>  $categories
     * @param  array<int, User>  $reviewers
     */
    private function seedProviders(array $cities, array $categories, array $reviewers): void
    {
        $providers = [
            // ── تصميم وإبداع ──────────────────────────────────────────────────────
            [
                'user_name' => 'محمد الطاهر البشير',
                'user_email' => 'mohammed.albashir.demo@delni.ly',
                'business_name' => 'استوديو البشير للتصميم',
                'bio' => 'مصمم جرافيك محترف بخبرة تزيد عن 8 سنوات في تصميم الهويات البصرية والمواد التسويقية. أعمل مع الشركات الصغيرة والمتوسطة في ليبيا لبناء هويات بصرية قوية تعكس قيمها وتتحدث إلى جمهورها.',
                'city' => 'tripoli',
                'category' => 'design-creative',
                'subcategories' => ['graphic-design', 'brand-identity'],
                'experience_years' => 8,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218913451234',
                'phone' => '+218913451234',
                'offers_remote_work' => true,
                'reviews' => [5, 5, 4, 5, 5, 5, 4],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(2)->toDateString(),
                'portfolio' => [
                    [
                        'title' => 'هوية بصرية لمطعم الزيتونة',
                        'short_description' => 'تصميم هوية كاملة تشمل الشعار والألوان والمطبوعات',
                        'description' => 'قمت بتصميم هوية بصرية متكاملة لمطعم الزيتونة في طرابلس، تشمل الشعار، لوحة الألوان، الخطوط، بطاقات العمل، المنيو، والتعبئة. الهوية تعكس الأصالة الليبية بلمسة عصرية.',
                        'sort_order' => 1,
                        'image_seeds' => [10, 20, 30],
                    ],
                    [
                        'title' => 'تصميم حملة سوشيال ميديا لمتجر الموضة',
                        'short_description' => 'منشورات إبداعية لحملة رمضانية',
                        'description' => 'سلسلة من 30 منشور مصمم لحملة رمضانية لمتجر ملابس في طرابلس، شملت منشورات إنستغرام وقصص وستوري بتصاميم متناسقة ومتسقة مع هوية العلامة التجارية.',
                        'sort_order' => 2,
                        'image_seeds' => [40, 50],
                    ],
                ],
            ],
            [
                'user_name' => 'نور الدين سالم البرغثي',
                'user_email' => 'noureddine.demo@delni.ly',
                'business_name' => 'البرغثي للإنتاج الإبداعي',
                'bio' => 'متخصص في تصميم الهوية البصرية وإنتاج الفيديو والموشن جرافيك. ساعدت أكثر من 50 شركة ليبية في بناء حضورها البصري القوي منذ 2016. أؤمن أن التصميم الجيد هو لغة تتحدث قبل أن تتكلم.',
                'city' => 'benghazi',
                'category' => 'design-creative',
                'subcategories' => ['brand-identity', 'video-motion'],
                'experience_years' => 9,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218924561234',
                'phone' => '+218924561234',
                'offers_remote_work' => true,
                'reviews' => [5, 5, 5, 4, 5, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'فيديو إعلاني لشركة عقارية',
                        'short_description' => 'فيديو موشن جرافيك 60 ثانية',
                        'description' => 'إنتاج فيديو إعلاني احترافي بتقنية الموشن جرافيك لشركة عقارية في بنغازي، يعرض مشاريع الشركة بأسلوب بصري جذاب.',
                        'sort_order' => 1,
                        'image_seeds' => [60, 70, 80],
                    ],
                    [
                        'title' => 'هوية بصرية لمركز طبي',
                        'short_description' => 'هوية متكاملة بتصميم نظيف وعصري',
                        'description' => 'تصميم هوية بصرية شاملة لمركز طبي متخصص تعكس الثقة والاحترافية، مع مراعاة سهولة التطبيق على الوسائط الرقمية والمطبوعة.',
                        'sort_order' => 2,
                        'image_seeds' => [90, 100],
                    ],
                ],
            ],
            [
                'user_name' => 'سارة القاسمي',
                'user_email' => 'sara.alqasmi.demo@delni.ly',
                'business_name' => 'سارة للتصميم الجرافيكي',
                'bio' => 'مصممة جرافيك مبدعة من مصراتة، متخصصة في تصميم منشورات السوشيال ميديا والمواد التسويقية الرقمية. أعشق دمج الجماليات العصرية مع الثقافة الليبية في أعمالي.',
                'city' => 'misrata',
                'category' => 'design-creative',
                'subcategories' => ['graphic-design', 'ui-ux-design'],
                'experience_years' => 4,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218925678901',
                'phone' => '+218925678901',
                'offers_remote_work' => true,
                'reviews' => [4, 5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'تصميم واجهة تطبيق توصيل',
                        'short_description' => 'UI/UX لتطبيق توصيل محلي',
                        'description' => 'تصميم واجهة مستخدم كاملة لتطبيق توصيل طلبات في مصراتة، تشمل شاشات الطلب والتتبع ولوحة التحكم بأسلوب حديث وسهل الاستخدام.',
                        'sort_order' => 1,
                        'image_seeds' => [110, 120, 130],
                    ],
                ],
            ],
            // ── برمجة وتقنية ──────────────────────────────────────────────────────
            [
                'user_name' => 'خالد محمد العربي',
                'user_email' => 'khaled.alarabi.demo@delni.ly',
                'business_name' => 'العربي للحلول التقنية',
                'bio' => 'شركة تقنية رائدة بخبرة 10 سنوات في بناء المنصات الرقمية والتطبيقات المحمولة. شريك تقني موثوق لعدد من الشركات الناشئة الليبية ومؤسسات القطاع الخاص. نؤمن بأن التقنية تغير حياة الليبيين.',
                'city' => 'tripoli',
                'category' => 'tech-programming',
                'subcategories' => ['web-development', 'app-development'],
                'experience_years' => 10,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218912345678',
                'phone' => '+218912345678',
                'offers_remote_work' => true,
                'reviews' => [5, 5, 5, 5, 5, 4, 5, 5],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(3)->toDateString(),
                'portfolio' => [
                    [
                        'title' => 'منصة تجارة إلكترونية متكاملة',
                        'short_description' => 'منصة بيع وشراء إلكتروني ليبية',
                        'description' => 'بناء منصة تجارة إلكترونية متكاملة للسوق الليبي تشمل نظام المدفوعات المحلية، إدارة المخزون، التوصيل، ولوحات التحكم للبائعين والمشترين.',
                        'sort_order' => 1,
                        'image_seeds' => [140, 150, 160],
                    ],
                    [
                        'title' => 'تطبيق حجز مواعيد للعيادات',
                        'short_description' => 'تطبيق iOS وAndroid لإدارة العيادات',
                        'description' => 'تطوير تطبيق محمول شامل لإدارة مواعيد العيادات الطبية، يشمل نظام الإشعارات، الملفات الطبية الرقمية، والفوترة التلقائية.',
                        'sort_order' => 2,
                        'image_seeds' => [170, 180],
                    ],
                ],
            ],
            [
                'user_name' => 'أحمد علي الزروق',
                'user_email' => 'ahmed.alzarouq.demo@delni.ly',
                'business_name' => 'الزروق للتطبيقات',
                'bio' => 'مطور تطبيقات موبايل بخبرة 5 سنوات في iOS وAndroid باستخدام Flutter وReact Native. أقدم حلولاً متكاملة للشركات التي تريد التوسع في القنوات الرقمية بتكلفة معقولة.',
                'city' => 'benghazi',
                'category' => 'tech-programming',
                'subcategories' => ['app-development', 'backend-development'],
                'experience_years' => 5,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218923456789',
                'phone' => '+218923456789',
                'offers_remote_work' => true,
                'reviews' => [4, 4, 5, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'تطبيق قراءة أخبار ليبيا',
                        'short_description' => 'تطبيق أخبار بتصميم حديث',
                        'description' => 'تطبيق إخباري يجمع أخبار ليبيا من عدة مصادر مع إمكانية التصفية والبحث والحفظ، متاح على iOS وAndroid.',
                        'sort_order' => 1,
                        'image_seeds' => [210, 220],
                    ],
                ],
            ],
            [
                'user_name' => 'أميرة الورفلي',
                'user_email' => 'amira.alwarfali.demo@delni.ly',
                'business_name' => 'أميرة كود',
                'bio' => 'مطورة خلفية متخصصة في PHP وLaravel وقواعد البيانات. أبني APIs وأنظمة إدارة بيانات موثوقة وقابلة للتوسع للشركات والمشاريع الناشئة الليبية والعربية.',
                'city' => 'zawiya',
                'category' => 'tech-programming',
                'subcategories' => ['backend-development', 'data-analytics'],
                'experience_years' => 6,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218917890123',
                'phone' => '+218917890123',
                'offers_remote_work' => true,
                'reviews' => [5, 5, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'API لمنصة عقارية',
                        'short_description' => 'RESTful API بـ Laravel لمنصة عقارات',
                        'description' => 'تطوير API شامل لمنصة عقارية تشمل إدارة العقارات، البحث المتقدم، نظام المفضلة، والإشعارات الفورية.',
                        'sort_order' => 1,
                        'image_seeds' => [230, 240, 250],
                    ],
                ],
            ],
            // ── تسويق رقمي ──────────────────────────────────────────────────────
            [
                'user_name' => 'فاطمة عمر الزياني',
                'user_email' => 'fatima.alziani.demo@delni.ly',
                'business_name' => 'فاطمة للتسويق الرقمي',
                'bio' => 'خبيرة تسويق رقمي بخبرة 7 سنوات في إدارة حسابات السوشيال ميديا وإنشاء المحتوى الاستراتيجي. ساعدت أكثر من 30 علامة تجارية ليبية في تنمية حضورها الرقمي وزيادة مبيعاتها.',
                'city' => 'tripoli',
                'category' => 'digital-marketing',
                'subcategories' => ['social-media-management', 'content-writing'],
                'experience_years' => 7,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218918901234',
                'phone' => '+218918901234',
                'offers_remote_work' => true,
                'reviews' => [5, 4, 5, 5, 5, 5],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(1)->toDateString(),
                'portfolio' => [
                    [
                        'title' => 'إدارة حسابات مطعم لمدة 6 أشهر',
                        'short_description' => 'نمو 300% في المتابعين',
                        'description' => 'إدارة كاملة لحسابات سوشيال ميديا مطعم في طرابلس لمدة ستة أشهر، مع إنتاج المحتوى اليومي وإدارة التعليقات والرسائل، مما أدى إلى نمو 300% في المتابعين وزيادة 40% في الحجوزات.',
                        'sort_order' => 1,
                        'image_seeds' => [260, 270, 280],
                    ],
                    [
                        'title' => 'استراتيجية محتوى لعلامة تجارية ملابس',
                        'short_description' => 'خطة محتوى شاملة لعلامة ملابس ليبية',
                        'description' => 'تطوير استراتيجية محتوى كاملة لعلامة ملابس ليبية ناشئة تشمل تقويم النشر، دليل الأسلوب البصري، والقصص الشهرية، مع متابعة التحليلات وتعديل الاستراتيجية شهريًا.',
                        'sort_order' => 2,
                        'image_seeds' => [290, 300],
                    ],
                ],
            ],
            [
                'user_name' => 'عمر سالم المنصوري',
                'user_email' => 'omar.almansoury.demo@delni.ly',
                'business_name' => 'المنصوري للإعلانات الرقمية',
                'bio' => 'متخصص في إدارة الإعلانات المدفوعة على Google وMeta وTikTok. أضع استراتيجيات إعلانية مبنية على البيانات لتحقيق أفضل عائد على الاستثمار للميزانيات الصغيرة والمتوسطة.',
                'city' => 'misrata',
                'category' => 'digital-marketing',
                'subcategories' => ['paid-ads', 'seo'],
                'experience_years' => 5,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218926789012',
                'phone' => '+218926789012',
                'offers_remote_work' => true,
                'reviews' => [4, 4, 3, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'حملة إعلانية لمتجر إلكتروني',
                        'short_description' => 'زيادة المبيعات 200% بميزانية محدودة',
                        'description' => 'إدارة حملة إعلانية متكاملة على Meta Ads لمتجر إلكتروني للإلكترونيات، حققت عائدًا 5x على الاستثمار خلال شهر واحد.',
                        'sort_order' => 1,
                        'image_seeds' => [310, 320],
                    ],
                ],
            ],
            [
                'user_name' => 'ليلى محمود الدرسي',
                'user_email' => 'layla.aldarsi.demo@delni.ly',
                'business_name' => 'ليلى للمحتوى الإبداعي',
                'bio' => 'كاتبة محتوى رقمي متخصصة في الكتابة الإبداعية والتسويقية باللغتين العربية والإنجليزية. أساعد العلامات التجارية في صياغة رسائلها بأسلوب يلامس جمهورها ويحقق أهدافها التسويقية.',
                'city' => 'al-bayda',
                'category' => 'digital-marketing',
                'subcategories' => ['content-writing', 'email-marketing'],
                'experience_years' => 3,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218929012345',
                'phone' => '+218929012345',
                'offers_remote_work' => true,
                'reviews' => [5, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'كتابة محتوى موقع شركة',
                        'short_description' => 'محتوى احترافي لموقع شركة تقنية',
                        'description' => 'كتابة محتوى متكامل لموقع شركة تقنية ليبية ناشئة، يشمل صفحات الخدمات، من نحن، والمدونة التقنية باللغة العربية والإنجليزية.',
                        'sort_order' => 1,
                        'image_seeds' => [330, 340],
                    ],
                ],
            ],
            // ── تصوير وإعلام ──────────────────────────────────────────────────────
            [
                'user_name' => 'عبدالرحمن القلال',
                'user_email' => 'abdulrahman.demo@delni.ly',
                'business_name' => 'القلال للتصوير الاحترافي',
                'bio' => 'مصور فوتوغرافي ومصور فيديو محترف بخبرة 8 سنوات في التصوير التجاري والحفلات والفعاليات. أقدم جلسات تصوير بجودة عالية تحكي قصة كل لحظة بتقنية عالية المستوى.',
                'city' => 'tripoli',
                'category' => 'photography-media',
                'subcategories' => ['photography', 'videography'],
                'experience_years' => 8,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218912000111',
                'phone' => '+218912000111',
                'offers_remote_work' => false,
                'reviews' => [5, 5, 5, 5, 4, 5],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(2)->toDateString(),
                'portfolio' => [
                    [
                        'title' => 'تصوير حفل زفاف فاخر',
                        'short_description' => 'ألبوم تصوير لأكثر من 300 صورة',
                        'description' => 'تصوير حفل زفاف فاخر في طرابلس يشمل جلسة التصوير الخارجية والداخلية والحفل الكامل، مع تسليم الألبوم الرقمي المعالج بعد أسبوع.',
                        'sort_order' => 1,
                        'image_seeds' => [350, 360, 370, 380],
                    ],
                    [
                        'title' => 'تصوير منتجات لمتجر عبر الإنترنت',
                        'short_description' => 'تصوير 50 منتج على خلفيات بيضاء وتركيبية',
                        'description' => 'جلسة تصوير منتجات متكاملة لمتجر ملابس وإكسسوارات، تشمل الخلفيات البيضاء للإيكوميرس وصور lifestyle المعبرة.',
                        'sort_order' => 2,
                        'image_seeds' => [390, 400],
                    ],
                ],
            ],
            [
                'user_name' => 'هيثم الزنتاني',
                'user_email' => 'haitham.demo@delni.ly',
                'business_name' => 'سماء ليبيا للتصوير الجوي',
                'bio' => 'متخصص في التصوير الجوي بالدرون مع شهادة معتمدة. أقدم لقطات جوية احترافية للمشاريع العقارية والمناسبات والمرافق الصناعية. مجهز بأحدث طائرات DJI.',
                'city' => 'misrata',
                'category' => 'photography-media',
                'subcategories' => ['drone-footage', 'videography'],
                'experience_years' => 4,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218931000222',
                'phone' => '+218931000222',
                'offers_remote_work' => false,
                'reviews' => [5, 5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'تصوير جوي لمشروع سكني',
                        'short_description' => 'فيديو جوي 4K لمجمع سكني',
                        'description' => 'تصوير جوي شامل لمجمع سكني في مصراتة يشمل لقطات من جميع الزوايا، مع تحرير الفيديو بالموسيقى والعناوين.',
                        'sort_order' => 1,
                        'image_seeds' => [410, 420, 430],
                    ],
                ],
            ],
            [
                'user_name' => 'نادية الشويهدي',
                'user_email' => 'nadia.photo.demo@delni.ly',
                'business_name' => 'نادية للمونتاج والإنتاج',
                'bio' => 'محررة فيديو ومنتجة بودكاست محترفة، متخصصة في إنتاج المحتوى المرئي والصوتي عالي الجودة. أعمل مع شركات ومؤسسات ليبية على إنتاج محتواها الرقمي بأسلوب احترافي يجذب الجمهور.',
                'city' => 'benghazi',
                'category' => 'photography-media',
                'subcategories' => ['video-editing', 'podcast-production'],
                'experience_years' => 5,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218921000333',
                'phone' => '+218921000333',
                'offers_remote_work' => true,
                'reviews' => [4, 5, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'إنتاج بودكاست أعمال ليبيا',
                        'short_description' => 'إنتاج وتوزيع بودكاست أسبوعي',
                        'description' => 'إنتاج بودكاست أعمال ليبيا الأسبوعي من التسجيل إلى التوزيع على جميع المنصات، بما يشمل التحرير والتصميم الصوتي وإدارة الحلقات.',
                        'sort_order' => 1,
                        'image_seeds' => [440, 450],
                    ],
                ],
            ],
            // ── صحة وجمال ──────────────────────────────────────────────────────
            [
                'user_name' => 'هالة الفيتوري',
                'user_email' => 'hala.demo@delni.ly',
                'business_name' => 'صالون هالة للعناية والجمال',
                'bio' => 'خبيرة تجميل وعناية بالبشرة بخبرة 10 سنوات، متخصصة في الميكب الطبيعي والعرائسي وعلاجات البشرة. أعمل مع أرقى العرائس والمشاهير في طرابلس.',
                'city' => 'tripoli',
                'category' => 'health-beauty',
                'subcategories' => ['makeup-artistry', 'skincare'],
                'experience_years' => 10,
                'type' => 'business',
                'provider_type' => 'studio',
                'whatsapp' => '+218916000444',
                'phone' => '+218916000444',
                'offers_remote_work' => false,
                'reviews' => [5, 5, 5, 5, 5, 5, 4, 5],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(2)->toDateString(),
                'portfolio' => [
                    [
                        'title' => 'ميكب عرائسي يوم الزفاف',
                        'short_description' => 'إطلالة عروس طرابلسية أصيلة وعصرية',
                        'description' => 'تصفيفة شعر وميكب كامل للعروس ووصيفاتها، بأسلوب يجمع الأصالة الليبية مع الإطلالة العصرية الرقيقة.',
                        'sort_order' => 1,
                        'image_seeds' => [460, 470, 480],
                    ],
                    [
                        'title' => 'جلسة تنظيف وعناية بالبشرة',
                        'short_description' => 'علاج بشرة شامل للبشرة الدهنية',
                        'description' => 'برنامج عناية بالبشرة لمدة شهر يشمل تنظيف الوجه العميق، التقشير، الترطيب وبروتوكول علاج حبوب البشرة الدهنية.',
                        'sort_order' => 2,
                        'image_seeds' => [490, 500],
                    ],
                ],
            ],
            [
                'user_name' => 'كريم الطرابلسي',
                'user_email' => 'karim.fitness.demo@delni.ly',
                'business_name' => 'كريم فيت - تدريب رياضي شخصي',
                'bio' => 'مدرب لياقة بدنية معتمد بخبرة 7 سنوات في التدريب الشخصي وتغذية الرياضيين. ساعدت أكثر من 200 شخص في تحقيق أهدافهم اللياقية في طرابلس. أعمل عبر الإنترنت وفي الصالات الرياضية.',
                'city' => 'tripoli',
                'category' => 'health-beauty',
                'subcategories' => ['fitness-coaching', 'nutrition-consulting'],
                'experience_years' => 7,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218916000555',
                'phone' => '+218916000555',
                'offers_remote_work' => true,
                'reviews' => [5, 5, 4, 5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'برنامج لياقة 12 أسبوع',
                        'short_description' => 'تحولات حقيقية مع عملاء طرابلس',
                        'description' => 'برنامج تدريبي مكثف مدته 12 أسبوعًا يجمع بين تمارين القوة والكارديو وخطة تغذية مخصصة، مع متابعة يومية وتعديل البرنامج أسبوعيًا حسب التقدم.',
                        'sort_order' => 1,
                        'image_seeds' => [510, 520, 530],
                    ],
                ],
            ],
            [
                'user_name' => 'إيمان بن سعيد',
                'user_email' => 'iman.hair.demo@delni.ly',
                'business_name' => 'كوافير إيمان - زليتن',
                'bio' => 'كوافيرة محترفة متخصصة في قص وتصفيف الشعر والعلاجات الكيراتينية. أقدم خدماتي في صالوني المتواضع بزليتن لأكثر من 8 سنوات مع حرص شديد على صحة شعر عميلاتي.',
                'city' => 'zliten',
                'category' => 'health-beauty',
                'subcategories' => ['hair-styling'],
                'experience_years' => 8,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218928000666',
                'phone' => '+218928000666',
                'offers_remote_work' => false,
                'reviews' => [5, 4, 5, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'تحولات شعر قبل وبعد',
                        'short_description' => 'تصفيف وصبغ احترافي',
                        'description' => 'معرض لأعمال التصفيف والصبغ الاحترافي لعميلات الصالون يُظهر التحول الكامل من الشعر التالف إلى الشعر الصحي اللامع.',
                        'sort_order' => 1,
                        'image_seeds' => [540, 550],
                    ],
                ],
            ],
            // ── خدمات المنزل والبناء ──────────────────────────────────────────
            [
                'user_name' => 'علي حسن المحجوبي',
                'user_email' => 'ali.almahjoubi.demo@delni.ly',
                'business_name' => 'المحجوبي للكهرباء والصيانة',
                'bio' => 'كهربائي محترف بخبرة 15 عامًا في تركيب وصيانة الأنظمة الكهربائية السكنية والتجارية في طرابلس وضواحيها. أقدم ضمانًا على جميع الأعمال لمدة سنة.',
                'city' => 'tripoli',
                'category' => 'home-services',
                'subcategories' => ['electrical'],
                'experience_years' => 15,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218916789012',
                'phone' => '+218916789012',
                'offers_remote_work' => false,
                'reviews' => [5, 4, 5, 5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'تمديد كهربائي فيلا سكنية',
                        'short_description' => 'تمديد كامل لفيلا من 4 طوابق',
                        'description' => 'تمديد كهربائي شامل لفيلا من 4 طوابق في طرابلس يشمل لوحة التوزيع الرئيسية وجميع التمديدات الداخلية والإضاءة والمقابس وأنظمة الأمان.',
                        'sort_order' => 1,
                        'image_seeds' => [560, 570],
                    ],
                ],
            ],
            [
                'user_name' => 'مختار سالم الورشفاني',
                'user_email' => 'mokhtar.demo@delni.ly',
                'business_name' => 'الورشفاني للسباكة',
                'bio' => 'سباك محترف بخبرة 12 عامًا في تركيب وإصلاح شبكات المياه والصرف الصحي للمنازل والمجمعات السكنية. خدمة طوارئ على مدار الساعة في مصراتة والمنطقة.',
                'city' => 'misrata',
                'category' => 'home-services',
                'subcategories' => ['plumbing'],
                'experience_years' => 12,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218920123456',
                'phone' => '+218920123456',
                'offers_remote_work' => false,
                'reviews' => [4, 3, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'تمديد شبكة مياه مجمع سكني',
                        'short_description' => 'تمديد كامل لـ 20 شقة سكنية',
                        'description' => 'تركيب وتمديد شبكة مياه كاملة لمجمع سكني من 20 وحدة في مصراتة، تشمل خطوط المياه الرئيسية والفرعية ونظام الضخ.',
                        'sort_order' => 1,
                        'image_seeds' => [580, 590],
                    ],
                ],
            ],
            [
                'user_name' => 'سعد الأكرمي',
                'user_email' => 'saad.decor.demo@delni.ly',
                'business_name' => 'الأكرمي للديكور والتصميم الداخلي',
                'bio' => 'مصمم داخلي ومنفذ ديكور بخبرة 9 سنوات، متخصص في تحويل المساحات العادية إلى بيئات راقية ومريحة. أعمل على الشقق والفيلات والمكاتب في غريان وطرابلس.',
                'city' => 'gharyan',
                'category' => 'home-services',
                'subcategories' => ['interior-design', 'carpentry'],
                'experience_years' => 9,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218914000777',
                'phone' => '+218914000777',
                'offers_remote_work' => false,
                'reviews' => [5, 5, 4, 5, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'تصميم وتنفيذ غرفة معيشة فاخرة',
                        'short_description' => 'تصميم عصري بلمسة مغربية أندلسية',
                        'description' => 'تصميم وتنفيذ كامل لغرفة معيشة بمساحة 60م² في فيلا بغريان، يجمع بين الطراز العصري والتراث الليبي بالأثاث المصنوع يدويًا والإضاءة التصميمية.',
                        'sort_order' => 1,
                        'image_seeds' => [600, 610, 620],
                    ],
                    [
                        'title' => 'تصميم مكتب شركة محاماة',
                        'short_description' => 'بيئة عمل راقية ومحترفة',
                        'description' => 'تصميم وتنفيذ ديكور مكتب شركة محاماة من القاعدة للسقف يشمل الحواجز الخشبية، الرفوف المكتبية المخصصة، وغرفة الاجتماعات الزجاجية.',
                        'sort_order' => 2,
                        'image_seeds' => [630, 640],
                    ],
                ],
            ],
            // ── تعليم وتدريب ──────────────────────────────────────────────────────
            [
                'user_name' => 'سامي عبدالله الميزوري',
                'user_email' => 'sami.demo@delni.ly',
                'business_name' => 'أكاديمية الميزوري التعليمية',
                'bio' => 'معلم ومدرب تعليمي متخصص في الرياضيات والفيزياء للمرحلتين الإعدادية والثانوية. خبرة 10 سنوات في التدريس وإعداد الطلاب لامتحانات النجاح والقبول الجامعي.',
                'city' => 'tripoli',
                'category' => 'education-training',
                'subcategories' => ['private-tutoring', 'educational-guidance'],
                'experience_years' => 10,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218915678901',
                'phone' => '+218915678901',
                'offers_remote_work' => true,
                'reviews' => [4, 4, 5, 5, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'دروس رياضيات للثانوية العامة',
                        'short_description' => 'نسبة نجاح 95% لطلابي',
                        'description' => 'منهج مبسط للرياضيات للصف الثاني والثالث الثانوي مع تمارين وافرة واختبارات أسبوعية، أثبت فاعليته مع أكثر من 200 طالب خلال 10 سنوات.',
                        'sort_order' => 1,
                        'image_seeds' => [650, 660],
                    ],
                ],
            ],
            [
                'user_name' => 'ناديا أحمد الفيتوري',
                'user_email' => 'nadia.alfituri.demo@delni.ly',
                'business_name' => 'ناديا للغات',
                'bio' => 'مدرسة لغات بخبرة 5 سنوات في تدريس الإنجليزية والفرنسية للمبتدئين والمتقدمين. دكتوراه في اللسانيات من جامعة تونس. أقدم دورات مكثفة ودروسًا خصوصية أونلاين وحضوريًا.',
                'city' => 'zliten',
                'category' => 'education-training',
                'subcategories' => ['language-courses', 'online-courses'],
                'experience_years' => 5,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218928901234',
                'phone' => '+218928901234',
                'offers_remote_work' => true,
                'reviews' => [5, 5, 5, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'دورة إنجليزية مكثفة 3 أشهر',
                        'short_description' => 'من المستوى صفر إلى B2',
                        'description' => 'برنامج تدريبي مكثف للغة الإنجليزية من الصفر حتى مستوى B2 في 3 أشهر، يشمل المحادثة والكتابة والقراءة والنحو مع جلسات أسبوعية.',
                        'sort_order' => 1,
                        'image_seeds' => [670, 680],
                    ],
                ],
            ],
            [
                'user_name' => 'يوسف إبراهيم الغرياني',
                'user_email' => 'youssef.alghiryani.demo@delni.ly',
                'business_name' => 'مركز الغرياني للتدريب المهني',
                'bio' => 'مدرب مهني معتمد متخصص في تأهيل الكوادر الشبابية للسوق المحلي والعربي في مجالات تقنية المعلومات وريادة الأعمال والمهارات الإدارية. شريك مع عدة منظمات دولية.',
                'city' => 'misrata',
                'category' => 'education-training',
                'subcategories' => ['vocational-training', 'online-courses'],
                'experience_years' => 7,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218927890123',
                'phone' => '+218927890123',
                'offers_remote_work' => true,
                'reviews' => [4, 5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'دورة ريادة الأعمال لـ 50 شاب',
                        'short_description' => 'تأهيل شبابي لسوق العمل',
                        'description' => 'برنامج تدريبي لريادة الأعمال استفاد منه 50 شاب ليبي بالتعاون مع منظمة دولية، تضمن مهارات الأعمال والتسويق وإدارة المشاريع الصغيرة.',
                        'sort_order' => 1,
                        'image_seeds' => [690, 700, 710],
                    ],
                ],
            ],
            // ── قانوني ومالي ──────────────────────────────────────────────────────
            [
                'user_name' => 'عبدالسلام حامد الزويتيني',
                'user_email' => 'abdelsalam.demo@delni.ly',
                'business_name' => 'مكتب الزويتيني للمحاماة',
                'bio' => 'محامٍ قانوني معتمد بخبرة 12 عامًا في القضايا التجارية والعقارية والعمالية. أعضو في نقابة المحامين الليبيين. أقدم استشارات قانونية موثوقة للأفراد والشركات.',
                'city' => 'tripoli',
                'category' => 'legal-accounting',
                'subcategories' => ['legal-services', 'contracts'],
                'experience_years' => 12,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218910123456',
                'phone' => '+218910123456',
                'offers_remote_work' => true,
                'reviews' => [4, 4, 5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'توثيق عقود شركة استثمارية',
                        'short_description' => 'عقود تأسيس وشراكة معتمدة',
                        'description' => 'إعداد وتوثيق عقود تأسيس شركة استثمارية متعددة الأطراف، تشمل عقد الشراكة ونظام الشركة والعقود الخدمية وفقًا للقانون الليبي.',
                        'sort_order' => 1,
                        'image_seeds' => [720, 730],
                    ],
                ],
            ],
            [
                'user_name' => 'محمود أحمد الكيلاني',
                'user_email' => 'mahmoud.kilani.demo@delni.ly',
                'business_name' => 'الكيلاني للمحاسبة والاستشارات',
                'bio' => 'محاسب قانوني معتمد، متخصص في إعداد القوائم المالية والتدقيق وخدمات الضريبة للشركات الصغيرة والمتوسطة. عضو اتحاد المحاسبين العرب.',
                'city' => 'benghazi',
                'category' => 'legal-accounting',
                'subcategories' => ['accounting', 'tax-consulting'],
                'experience_years' => 8,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218921234567',
                'phone' => '+218921234567',
                'offers_remote_work' => true,
                'reviews' => [4, 5, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'قوائم مالية سنوية لشركة تجارية',
                        'short_description' => 'تدقيق وإعداد التقارير المالية',
                        'description' => 'إعداد القوائم المالية السنوية الكاملة لشركة تجارية في بنغازي تشمل ميزانية عمومية ومحاسبة الأرباح والخسائر والتدفق النقدي مع تقرير المدقق.',
                        'sort_order' => 1,
                        'image_seeds' => [740, 750],
                    ],
                ],
            ],
            [
                'user_name' => 'إيمان الشلابي',
                'user_email' => 'iman.shalabi.demo@delni.ly',
                'business_name' => 'إيمان للاستشارات الضريبية والمالية',
                'bio' => 'مستشارة ضريبية ومالية بخبرة 6 سنوات، متخصصة في تقديم حلول ضريبية ومالية للشركات والمنشآت التجارية. أساعد عملائي على التخطيط المالي وتعظيم الكفاءة الضريبية.',
                'city' => 'sabha',
                'category' => 'legal-accounting',
                'subcategories' => ['tax-consulting', 'financial-planning'],
                'experience_years' => 6,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218914567890',
                'phone' => '+218914567890',
                'offers_remote_work' => true,
                'reviews' => [4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'تخطيط مالي لمطعم ناشئ',
                        'short_description' => 'دراسة جدوى وخطة مالية 3 سنوات',
                        'description' => 'إعداد خطة مالية شاملة لمطعم ناشئ تشمل دراسة الجدوى، توقعات الإيرادات، خطة التمويل، والتخطيط الضريبي للسنوات الثلاث الأولى.',
                        'sort_order' => 1,
                        'image_seeds' => [760, 770],
                    ],
                ],
            ],
            // ── نقل ولوجستيك ──────────────────────────────────────────────────────
            [
                'user_name' => 'وليد عبدالله الترهوني',
                'user_email' => 'walid.transport.demo@delni.ly',
                'business_name' => 'الترهوني للشحن والنقل',
                'bio' => 'شركة شحن ولوجستيك بخبرة 15 عامًا في نقل البضائع داخل ليبيا وعبر الحدود. أسطول من 20 شاحنة جاهزة لخدمة الشركات التجارية والأفراد في جميع المدن الليبية.',
                'city' => 'tripoli',
                'category' => 'transport-logistics',
                'subcategories' => ['freight-shipping', 'cargo-customs'],
                'experience_years' => 15,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218913000888',
                'phone' => '+218913000888',
                'offers_remote_work' => false,
                'reviews' => [4, 5, 4, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'نقل بضائع من تونس إلى ليبيا',
                        'short_description' => 'شحنة تجارية عبر الحدود',
                        'description' => 'إدارة شحنة تجارية ضخمة من منسوجات وإلكترونيات من تونس إلى طرابلس، تشمل التخليص الجمركي والتوزيع النهائي على المخازن.',
                        'sort_order' => 1,
                        'image_seeds' => [780, 790],
                    ],
                ],
            ],
            [
                'user_name' => 'مصطفى العجيلي',
                'user_email' => 'mustafa.moving.demo@delni.ly',
                'business_name' => 'العجيلي لنقل العفش والأثاث',
                'bio' => 'خدمة نقل أثاث ومنازل بخبرة 8 سنوات، مزود بسيارات نقل مجهزة وعمال متخصصين في الفك والتركيب والتغليف الآمن. نغطي جميع مدن غرب ليبيا.',
                'city' => 'tripoli',
                'category' => 'transport-logistics',
                'subcategories' => ['moving-services'],
                'experience_years' => 8,
                'type' => 'individual',
                'provider_type' => 'other',
                'whatsapp' => '+218913000999',
                'phone' => '+218913000999',
                'offers_remote_work' => false,
                'reviews' => [4, 4, 5, 3, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'نقل منزل كامل من طرابلس إلى الزاوية',
                        'short_description' => 'خدمة نقل شاملة في يوم واحد',
                        'description' => 'نقل أثاث وأغراض منزل كامل من طرابلس إلى الزاوية في يوم واحد، مع تغليف الأثاث الخشبي والزجاجي وإعادة التركيب في الموقع الجديد.',
                        'sort_order' => 1,
                        'image_seeds' => [800, 810],
                    ],
                ],
            ],
            [
                'user_name' => 'أنس البوصيري',
                'user_email' => 'anas.delivery.demo@delni.ly',
                'business_name' => 'سريع - خدمة التوصيل اليومي',
                'bio' => 'خدمة توصيل سريع للطرود والمشتريات والوثائق في طبرق والمنطقة الشرقية. نضمن التوصيل في نفس اليوم داخل المدينة وفي 24 ساعة للمناطق المجاورة.',
                'city' => 'tobruk',
                'category' => 'transport-logistics',
                'subcategories' => ['courier-delivery', 'passenger-transport'],
                'experience_years' => 3,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218924000100',
                'phone' => '+218924000100',
                'offers_remote_work' => false,
                'reviews' => [5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'توصيل طلبات متجر إلكتروني',
                        'short_description' => 'شراكة مع متجر ملابس في طبرق',
                        'description' => 'توصيل يومي لطلبات متجر ملابس إلكتروني في طبرق، بمعدل 30-50 طرد يوميًا مع نظام تتبع ومستوى رضا عملاء 98%.',
                        'sort_order' => 1,
                        'image_seeds' => [820, 830],
                    ],
                ],
            ],
            // ── غذاء وضيافة ──────────────────────────────────────────────────────
            [
                'user_name' => 'أبوبكر الجهاني',
                'user_email' => 'abubakr.chef.demo@delni.ly',
                'business_name' => 'شيف أبوبكر - المطبخ الليبي الأصيل',
                'bio' => 'طباخ خاص محترف بخبرة 12 عامًا في المطبخ الليبي والمغاربي. أقدم خدمات الطبخ الخاص للمناسبات الكبيرة والصغيرة والعائلات في بنغازي، مع توصيل الوجبات يوميًا للمنازل.',
                'city' => 'benghazi',
                'category' => 'food-hospitality',
                'subcategories' => ['private-chef', 'event-catering'],
                'experience_years' => 12,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218921000200',
                'phone' => '+218921000200',
                'offers_remote_work' => false,
                'reviews' => [5, 5, 5, 5, 5, 4, 5],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(1)->toDateString(),
                'portfolio' => [
                    [
                        'title' => 'وليمة عرس تقليدية لـ 200 شخص',
                        'short_description' => 'مأكولات ليبية أصيلة لحفل زفاف',
                        'description' => 'إعداد وتقديم وليمة عرس تقليدية كاملة لـ 200 شخص في بنغازي، تشمل الأطباق الليبية الأصيلة كالكسكسي والبازين والمعكرونة الليبية والحلويات التقليدية.',
                        'sort_order' => 1,
                        'image_seeds' => [840, 850, 860],
                    ],
                    [
                        'title' => 'وجبات منزلية أسبوعية لعائلة',
                        'short_description' => 'اشتراك أسبوعي بـ 5 وجبات يومية',
                        'description' => 'خدمة توصيل وجبات منزلية مطبوخة يوميًا لعائلة في بنغازي، مع تنوع في القائمة الأسبوعية لتشمل المطبخ الليبي والعربي والمتوسطي.',
                        'sort_order' => 2,
                        'image_seeds' => [870, 880],
                    ],
                ],
            ],
            [
                'user_name' => 'مريم الطرشني',
                'user_email' => 'mariam.sweets.demo@delni.ly',
                'business_name' => 'حلويات مريم - زليتن',
                'bio' => 'صانعة حلويات وكعك متخصصة في الحلويات الليبية التقليدية والكيك المصمم للمناسبات. أقدم طلبيات مخصصة للأعراس والأعياد والحفلات من مطبخي الخاص في زليتن.',
                'city' => 'zliten',
                'category' => 'food-hospitality',
                'subcategories' => ['pastry-bakery', 'event-catering'],
                'experience_years' => 6,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218928000200',
                'phone' => '+218928000200',
                'offers_remote_work' => false,
                'reviews' => [5, 5, 5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'كيك عرس مصمم لحفل خطوبة',
                        'short_description' => 'كيك متعدد الطوابق بالتصميم الليبي',
                        'description' => 'كيك خطوبة فاخر من 4 طوابق بتزيين يدوي بالأزهار السكرية والخط العربي، مع تقديم بوفيه حلويات مصغر من الشعيبيات والزلابية والأسامير.',
                        'sort_order' => 1,
                        'image_seeds' => [890, 900, 910],
                    ],
                ],
            ],
            [
                'user_name' => 'رشيد الأمين',
                'user_email' => 'rashid.restaurant.demo@delni.ly',
                'business_name' => 'مطعم الأمين - طرابلس',
                'bio' => 'مطعم عائلي راقٍ في طرابلس يقدم أشهى الأكلات الليبية والمتوسطية بخامات طازجة يوميًا. نخدم أكثر من 150 زبونًا يوميًا ونقدم خدمة التوصيل وضيافة المناسبات.',
                'city' => 'tripoli',
                'category' => 'food-hospitality',
                'subcategories' => ['restaurant-catering', 'event-catering'],
                'experience_years' => 11,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218913000300',
                'phone' => '+218913000300',
                'offers_remote_work' => false,
                'reviews' => [5, 4, 5, 5, 4, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
                'portfolio' => [
                    [
                        'title' => 'ضيافة حفل شركة لـ 500 شخص',
                        'short_description' => 'تقديم طعام وبوفيه فاخر',
                        'description' => 'تقديم خدمة ضيافة كاملة لحفل سنوي لشركة كبرى في طرابلس لـ 500 شخص، تشمل البوفيه الساخن والبارد والحلويات والمشروبات مع طاقم الخدمة الكامل.',
                        'sort_order' => 1,
                        'image_seeds' => [920, 930, 940],
                    ],
                    [
                        'title' => 'وجبات الغداء اليومية لشركة',
                        'short_description' => 'اشتراك شهري لـ 80 موظف',
                        'description' => 'توصيل وجبات غداء يومية لـ 80 موظف في شركة مقاولات بطرابلس، مع تنويع القائمة أسبوعيًا ومراعاة احتياجات المشترك الغذائية.',
                        'sort_order' => 2,
                        'image_seeds' => [950, 960],
                    ],
                ],
            ],
        ];

        foreach ($providers as $data) {
            $this->createProvider($data, $cities, $categories, $reviewers);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, City>  $cities
     * @param  array<string, array{category: Category, subcategories: array<string, Subcategory>}>  $categories
     * @param  array<int, User>  $reviewers
     */
    private function createProvider(array $data, array $cities, array $categories, array $reviewers): void
    {
        $user = User::firstOrCreate(
            ['email' => $data['user_email']],
            [
                'name' => $data['user_name'],
                'password' => Hash::make('provider123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'is_suspended' => false,
            ],
        );

        $city = $cities[$data['city']];
        $categoryEntry = $categories[$data['category']];
        $category = $categoryEntry['category'];

        $slug = Str::slug($data['business_name']);
        if (Profile::where('slug', $slug)->whereNot('user_id', $user->id)->exists()) {
            $slug .= '-'.$user->id;
        }

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $data['business_name'],
                'bio' => $data['bio'],
                'slug' => $slug,
                'type' => $data['type'],
                'provider_type' => $data['provider_type'],
                'city_id' => $city->id,
                'category_id' => $category->id,
                'whatsapp' => $data['whatsapp'],
                'phone' => $data['phone'],
                'experience_years' => $data['experience_years'],
                'offers_remote_work' => $data['offers_remote_work'],
                'is_complete' => true,
                'provider_access_ends_at' => Carbon::today()->addYear(),
            ],
        );

        $subcategoryIds = collect($data['subcategories'])
            ->filter(fn (string $subSlug) => isset($categoryEntry['subcategories'][$subSlug]))
            ->map(fn (string $subSlug) => $categoryEntry['subcategories'][$subSlug]->id)
            ->all();

        $profile->subcategories()->syncWithoutDetaching($subcategoryIds);

        if (! $user->hasRole('provider')) {
            $user->assignRole('provider');
        }

        $reviews = $data['reviews'];
        $count = count($reviews);
        $avg = $count > 0 ? round(array_sum($reviews) / $count, 1) : 0.0;
        $isTopRated = $avg >= 4.5 && $count >= 5;

        ProfileStats::updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'reviews_count' => $count,
                'rating_avg' => $avg,
                'is_top_rated' => $isTopRated,
                'is_homepage_featured' => $data['is_homepage_featured'],
                'homepage_featured_until' => $data['homepage_featured_until'],
                'is_top_search' => false,
                'top_search_until' => null,
                'is_top_category' => false,
                'top_category_until' => null,
                'is_top_subcategory' => false,
                'top_subcategory_until' => null,
            ],
        );

        if ($count > 0 && ! $profile->reviews()->exists()) {
            foreach ($reviews as $index => $rating) {
                $reviewer = $reviewers[$index % count($reviewers)];

                if ($profile->reviews()->where('user_id', $reviewer->id)->exists()) {
                    continue;
                }

                Review::create([
                    'profile_id' => $profile->id,
                    'user_id' => $reviewer->id,
                    'rating' => $rating,
                    'status' => ReviewStatus::APPROVED,
                    'comment' => $this->demoComment($rating),
                    'created_at' => now()->subDays(rand(1, 120)),
                ]);
            }
        }

        if (! empty($data['portfolio']) && ! $profile->portfolioItems()->exists()) {
            foreach ($data['portfolio'] as $itemData) {
                $item = PortfolioItem::create([
                    'profile_id' => $profile->id,
                    'title' => $itemData['title'],
                    'short_description' => $itemData['short_description'],
                    'description' => $itemData['description'],
                    'sort_order' => $itemData['sort_order'],
                    'is_active' => true,
                ]);

                foreach ($itemData['image_seeds'] as $sortIndex => $seed) {
                    $imagePath = $this->downloadDemoImage($seed);
                    if ($imagePath) {
                        PortfolioImage::create([
                            'portfolio_item_id' => $item->id,
                            'path' => $imagePath,
                            'alt' => $itemData['title'],
                            'sort_order' => $sortIndex,
                        ]);
                    }
                }
            }
        }
    }

    private function downloadDemoImage(int $seed): ?string
    {
        $fileName = "portfolio/demo-{$seed}.jpg";

        if (Storage::disk('public')->exists($fileName)) {
            return $fileName;
        }

        Storage::disk('public')->makeDirectory('portfolio');

        try {
            $context = stream_context_create(['http' => ['timeout' => 10]]);
            $content = @file_get_contents("https://picsum.photos/seed/{$seed}/800/600", false, $context);

            if ($content === false || strlen($content) < 1000) {
                return null;
            }

            Storage::disk('public')->put($fileName, $content);

            return $fileName;
        } catch (\Throwable) {
            return null;
        }
    }

    private function demoComment(int $rating): string
    {
        $comments = [
            5 => [
                'خدمة ممتازة وجودة عالية، أنصح بالتعامل معهم بشدة.',
                'تعامل راقي ومحترف، سأعود بالتأكيد ولن أتردد في التوصية.',
                'أفضل مزود خدمة تعاملت معه حتى الآن، شكرًا جزيلًا.',
                'جودة الشغل فوق التوقعات مع التزام تام بالمواعيد.',
                'نتائج رائعة في وقت قياسي، موصى به بشدة لكل من يبحث عن التميز.',
                'احترافية عالية وتواصل ممتاز طوال فترة العمل.',
            ],
            4 => [
                'عمل جيد جدًا وخدمة محترمة، أنصح بالتعامل معهم.',
                'راضٍ جدًا عن الخدمة بشكل عام، هناك دائمًا مجال للتطوير.',
                'تجربة إيجابية وسأتعامل معهم مرة أخرى دون تردد.',
                'جودة ممتازة بسعر مناسب، قيمة حقيقية مقابل المال.',
                'اهتمام بالتفاصيل ورغبة واضحة في إرضاء العميل.',
            ],
            3 => [
                'خدمة متوسطة بشكل عام، تحتاج إلى تحسين في التواصل.',
                'النتيجة مقبولة لكن التأخير في التسليم كان مزعجًا.',
                'يحتاج إلى المزيد من الاهتمام بالتفاصيل والالتزام بالمواعيد.',
            ],
        ];

        $pool = $comments[$rating] ?? $comments[4];

        return $pool[array_rand($pool)];
    }
}
