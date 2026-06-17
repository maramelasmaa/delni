<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $cities = $this->seedCities();
        $categories = $this->seedCategories();
        $reviewers = $this->seedReviewers();
        $this->seedProviders($cities, $categories, $reviewers);
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
        ];

        $result = [];
        foreach ($data as $row) {
            $city = City::updateOrCreate(['slug' => $row['slug']], $row + ['is_active' => true]);
            $result[$row['slug']] = $city;
        }

        return $result;
    }

    /**
     * @return array<string, array{category: Category, subcategories: array<string, Subcategory>}>
     */
    private function seedCategories(): array
    {
        $data = [
            [
                'name' => 'Design & Creative',
                'name_ar' => 'تصميم وإبداع',
                'slug' => 'design-creative',
                'icon' => 'app-edit',
                'sort_order' => 1,
                'subcategories' => [
                    ['name' => 'Graphic Design', 'name_ar' => 'تصميم جرافيك', 'slug' => 'graphic-design', 'sort_order' => 1],
                    ['name' => 'Web Design', 'name_ar' => 'تصميم مواقع', 'slug' => 'web-design', 'sort_order' => 2],
                    ['name' => 'Brand Identity', 'name_ar' => 'هوية بصرية', 'slug' => 'brand-identity', 'sort_order' => 3],
                    ['name' => 'Video & Motion', 'name_ar' => 'فيديو وموشن جرافيك', 'slug' => 'video-motion', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Tech & Programming',
                'name_ar' => 'برمجة وتقنية',
                'slug' => 'tech-programming',
                'icon' => 'app-stack',
                'sort_order' => 2,
                'subcategories' => [
                    ['name' => 'Web Development', 'name_ar' => 'تطوير مواقع', 'slug' => 'web-development', 'sort_order' => 1],
                    ['name' => 'App Development', 'name_ar' => 'تطوير تطبيقات', 'slug' => 'app-development', 'sort_order' => 2],
                    ['name' => 'Backend Development', 'name_ar' => 'برمجة خلفية', 'slug' => 'backend-development', 'sort_order' => 3],
                    ['name' => 'Cybersecurity', 'name_ar' => 'أمن المعلومات', 'slug' => 'cybersecurity', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Digital Marketing',
                'name_ar' => 'تسويق رقمي',
                'slug' => 'digital-marketing',
                'icon' => 'app-users',
                'sort_order' => 3,
                'subcategories' => [
                    ['name' => 'Social Media Management', 'name_ar' => 'إدارة سوشيال ميديا', 'slug' => 'social-media-management', 'sort_order' => 1],
                    ['name' => 'Paid Ads', 'name_ar' => 'إعلانات مدفوعة', 'slug' => 'paid-ads', 'sort_order' => 2],
                    ['name' => 'SEO', 'name_ar' => 'تحسين محركات البحث', 'slug' => 'seo', 'sort_order' => 3],
                    ['name' => 'Content Writing', 'name_ar' => 'كتابة محتوى', 'slug' => 'content-writing', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Legal & Accounting',
                'name_ar' => 'قانوني ومحاسبي',
                'slug' => 'legal-accounting',
                'icon' => 'app-shield',
                'sort_order' => 4,
                'subcategories' => [
                    ['name' => 'Legal Services', 'name_ar' => 'محاماة', 'slug' => 'legal-services', 'sort_order' => 1],
                    ['name' => 'Accounting', 'name_ar' => 'محاسبة', 'slug' => 'accounting', 'sort_order' => 2],
                    ['name' => 'Tax Consulting', 'name_ar' => 'استشارات ضريبية', 'slug' => 'tax-consulting', 'sort_order' => 3],
                    ['name' => 'Contracts', 'name_ar' => 'توثيق عقود', 'slug' => 'contracts', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Education & Training',
                'name_ar' => 'تعليم وتدريب',
                'slug' => 'education-training',
                'icon' => 'app-document',
                'sort_order' => 5,
                'subcategories' => [
                    ['name' => 'Private Tutoring', 'name_ar' => 'دروس خصوصية', 'slug' => 'private-tutoring', 'sort_order' => 1],
                    ['name' => 'Language Courses', 'name_ar' => 'دورات لغات', 'slug' => 'language-courses', 'sort_order' => 2],
                    ['name' => 'Vocational Training', 'name_ar' => 'تدريب مهني', 'slug' => 'vocational-training', 'sort_order' => 3],
                    ['name' => 'Educational Guidance', 'name_ar' => 'إرشاد تعليمي', 'slug' => 'educational-guidance', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Home Services',
                'name_ar' => 'خدمات المنزل',
                'slug' => 'home-services',
                'icon' => 'app-home',
                'sort_order' => 6,
                'subcategories' => [
                    ['name' => 'Plumbing', 'name_ar' => 'سباكة', 'slug' => 'plumbing', 'sort_order' => 1],
                    ['name' => 'Electrical', 'name_ar' => 'كهرباء', 'slug' => 'electrical', 'sort_order' => 2],
                    ['name' => 'Carpentry', 'name_ar' => 'نجارة', 'slug' => 'carpentry', 'sort_order' => 3],
                    ['name' => 'HVAC', 'name_ar' => 'تكييف وتبريد', 'slug' => 'hvac', 'sort_order' => 4],
                ],
            ],
        ];

        $result = [];
        foreach ($data as $catData) {
            $subcategoryData = $catData['subcategories'];
            unset($catData['subcategories']);

            $category = Category::updateOrCreate(['slug' => $catData['slug']], $catData + ['is_active' => true]);
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
            // ── تصميم وإبداع ─────────────────────────────────────────────
            [
                'user_name' => 'محمد الطاهر البشير',
                'user_email' => 'mohammed.albashir.demo@delni.ly',
                'business_name' => 'استوديو البشير للتصميم',
                'bio' => 'مصمم جرافيك محترف بخبرة تزيد عن 8 سنوات في تصميم الهويات البصرية والمواد التسويقية. أعمل مع الشركات الصغيرة والمتوسطة في ليبيا لبناء هويات بصرية قوية تعكس قيمها.',
                'city' => 'tripoli',
                'category' => 'design-creative',
                'subcategories' => ['graphic-design', 'brand-identity'],
                'experience_years' => 8,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218913451234',
                'phone' => '+218913451234',
                'reviews' => [5, 5, 4, 5, 5, 5, 4],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(2)->toDateString(),
            ],
            [
                'user_name' => 'نور الدين سالم البرغثي',
                'user_email' => 'noureddine.demo@delni.ly',
                'business_name' => 'البرغثي للإنتاج الإبداعي',
                'bio' => 'متخصص في تصميم الهوية البصرية وإنتاج الفيديو والموشن جرافيك. ساعدت أكثر من 50 شركة ليبية في بناء حضورها البصري منذ 2016.',
                'city' => 'benghazi',
                'category' => 'design-creative',
                'subcategories' => ['brand-identity', 'video-motion'],
                'experience_years' => 9,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218924561234',
                'phone' => '+218924561234',
                'reviews' => [5, 5, 5, 4, 5, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            [
                'user_name' => 'سارة القاسمي',
                'user_email' => 'sara.alqasmi.demo@delni.ly',
                'business_name' => 'سارة للتصميم الجرافيكي',
                'bio' => 'مصممة جرافيك مبدعة من مصراتة، متخصصة في تصميم منشورات السوشيال ميديا والمواد التسويقية الرقمية.',
                'city' => 'misrata',
                'category' => 'design-creative',
                'subcategories' => ['graphic-design'],
                'experience_years' => 4,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218925678901',
                'phone' => '+218925678901',
                'reviews' => [4, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            // ── برمجة وتقنية ──────────────────────────────────────────────
            [
                'user_name' => 'خالد محمد العربي',
                'user_email' => 'khaled.alarabi.demo@delni.ly',
                'business_name' => 'العربي للحلول التقنية',
                'bio' => 'مطور ويب وتطبيقات محترف، خبرة 10 سنوات في بناء المنصات الرقمية والتطبيقات المحمولة. شريك تقني لعدد من الشركات الناشئة الليبية.',
                'city' => 'tripoli',
                'category' => 'tech-programming',
                'subcategories' => ['web-development', 'app-development'],
                'experience_years' => 10,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218912345678',
                'phone' => '+218912345678',
                'reviews' => [5, 5, 5, 5, 5, 4, 5, 5],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(3)->toDateString(),
            ],
            [
                'user_name' => 'أحمد علي الزروق',
                'user_email' => 'ahmed.alzarouq.demo@delni.ly',
                'business_name' => 'الزروق للتطبيقات',
                'bio' => 'مطور تطبيقات موبايل بخبرة 5 سنوات في iOS وAndroid. أقدم حلولاً متكاملة للشركات التي تريد التوسع في القنوات الرقمية.',
                'city' => 'benghazi',
                'category' => 'tech-programming',
                'subcategories' => ['app-development'],
                'experience_years' => 5,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218923456789',
                'phone' => '+218923456789',
                'reviews' => [4, 4, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            [
                'user_name' => 'أميرة الورفلي',
                'user_email' => 'amira.alwarfali.demo@delni.ly',
                'business_name' => 'أميرة كود',
                'bio' => 'مطورة خلفية متخصصة في PHP وLaravel وقواعد البيانات. أبني APIs وأنظمة إدارة بيانات للشركات والمشاريع الناشئة.',
                'city' => 'zawiya',
                'category' => 'tech-programming',
                'subcategories' => ['backend-development'],
                'experience_years' => 6,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218917890123',
                'phone' => '+218917890123',
                'reviews' => [5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            // ── تسويق رقمي ────────────────────────────────────────────────
            [
                'user_name' => 'فاطمة عمر الزياني',
                'user_email' => 'fatima.alziani.demo@delni.ly',
                'business_name' => 'فاطمة للتسويق الرقمي',
                'bio' => 'خبيرة تسويق رقمي بخبرة 7 سنوات في إدارة حسابات السوشيال ميديا وإنشاء المحتوى. ساعدت أكثر من 30 علامة تجارية ليبية في تنمية حضورها الرقمي.',
                'city' => 'tripoli',
                'category' => 'digital-marketing',
                'subcategories' => ['social-media-management', 'content-writing'],
                'experience_years' => 7,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218918901234',
                'phone' => '+218918901234',
                'reviews' => [5, 4, 5, 5, 5],
                'is_homepage_featured' => true,
                'homepage_featured_until' => Carbon::today()->addMonths(1)->toDateString(),
            ],
            [
                'user_name' => 'عمر سالم المنصوري',
                'user_email' => 'omar.almansoury.demo@delni.ly',
                'business_name' => 'المنصوري للإعلانات الرقمية',
                'bio' => 'متخصص في إدارة الإعلانات المدفوعة على Google وFacebook وInstagram. أضع استراتيجيات إعلانية مبنية على البيانات لتحقيق أفضل عائد على الاستثمار.',
                'city' => 'misrata',
                'category' => 'digital-marketing',
                'subcategories' => ['paid-ads', 'seo'],
                'experience_years' => 5,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218926789012',
                'phone' => '+218926789012',
                'reviews' => [3, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            [
                'user_name' => 'ليلى محمود الدرسي',
                'user_email' => 'layla.aldarsi.demo@delni.ly',
                'business_name' => 'ليلى للمحتوى الإبداعي',
                'bio' => 'كاتبة محتوى رقمي متخصصة في الكتابة الإبداعية والتسويقية باللغتين العربية والإنجليزية. أساعد العلامات التجارية في صياغة رسائلها بأسلوب يلامس جمهورها.',
                'city' => 'al-bayda',
                'category' => 'digital-marketing',
                'subcategories' => ['content-writing'],
                'experience_years' => 3,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218929012345',
                'phone' => '+218929012345',
                'reviews' => [],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            // ── قانوني ومحاسبي ────────────────────────────────────────────
            [
                'user_name' => 'عبدالسلام حامد الزويتيني',
                'user_email' => 'abdelsalam.demo@delni.ly',
                'business_name' => 'مكتب الزويتيني للمحاماة',
                'bio' => 'محامٍ قانوني معتمد بخبرة 12 عامًا في القضايا التجارية والعقارية والعمالية. أقدم استشارات قانونية موثوقة للأفراد والشركات في ليبيا.',
                'city' => 'tripoli',
                'category' => 'legal-accounting',
                'subcategories' => ['legal-services', 'contracts'],
                'experience_years' => 12,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218910123456',
                'phone' => '+218910123456',
                'reviews' => [4, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            [
                'user_name' => 'محمود أحمد الكيلاني',
                'user_email' => 'mahmoud.kilani.demo@delni.ly',
                'business_name' => 'الكيلاني للمحاسبة والاستشارات',
                'bio' => 'محاسب قانوني معتمد، متخصص في إعداد القوائم المالية والتدقيق وخدمات الضريبة للشركات الصغيرة والمتوسطة.',
                'city' => 'benghazi',
                'category' => 'legal-accounting',
                'subcategories' => ['accounting', 'tax-consulting'],
                'experience_years' => 8,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218921234567',
                'phone' => '+218921234567',
                'reviews' => [4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            [
                'user_name' => 'إيمان الشلابي',
                'user_email' => 'iman.shalabi.demo@delni.ly',
                'business_name' => 'إيمان للاستشارات الضريبية',
                'bio' => 'مستشارة ضريبية ومالية بخبرة 6 سنوات، متخصصة في تقديم حلول ضريبية للشركات والمنشآت التجارية في جنوب ليبيا.',
                'city' => 'sabha',
                'category' => 'legal-accounting',
                'subcategories' => ['tax-consulting'],
                'experience_years' => 6,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218914567890',
                'phone' => '+218914567890',
                'reviews' => [],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            // ── تعليم وتدريب ──────────────────────────────────────────────
            [
                'user_name' => 'سامي عبدالله الميزوري',
                'user_email' => 'sami.demo@delni.ly',
                'business_name' => 'أكاديمية الميزوري التعليمية',
                'bio' => 'معلم ومدرب تعليمي متخصص في الرياضيات والفيزياء للمرحلتين الإعدادية والثانوية. أقدم دروسًا خصوصية فردية وجماعية.',
                'city' => 'tripoli',
                'category' => 'education-training',
                'subcategories' => ['private-tutoring', 'educational-guidance'],
                'experience_years' => 10,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218915678901',
                'phone' => '+218915678901',
                'reviews' => [4, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            [
                'user_name' => 'ناديا أحمد الفيتوري',
                'user_email' => 'nadia.alfituri.demo@delni.ly',
                'business_name' => 'ناديا للغات',
                'bio' => 'مدرسة لغات بخبرة 5 سنوات في تدريس الإنجليزية والفرنسية للمبتدئين والمتقدمين. أقدم دورات مكثفة ودروسًا خصوصية عبر الإنترنت وحضوريًا.',
                'city' => 'zliten',
                'category' => 'education-training',
                'subcategories' => ['language-courses'],
                'experience_years' => 5,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218928901234',
                'phone' => '+218928901234',
                'reviews' => [5, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            [
                'user_name' => 'يوسف إبراهيم الغرياني',
                'user_email' => 'youssef.alghiryani.demo@delni.ly',
                'business_name' => 'مركز الغرياني للتدريب المهني',
                'bio' => 'مدرب مهني معتمد متخصص في تأهيل الكوادر الشبابية للسوق المحلي والعربي في مجالات تقنية المعلومات والأعمال الإدارية.',
                'city' => 'misrata',
                'category' => 'education-training',
                'subcategories' => ['vocational-training'],
                'experience_years' => 7,
                'type' => 'business',
                'provider_type' => 'company',
                'whatsapp' => '+218927890123',
                'phone' => '+218927890123',
                'reviews' => [4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            // ── خدمات المنزل ──────────────────────────────────────────────
            [
                'user_name' => 'علي حسن المحجوبي',
                'user_email' => 'ali.almahjoubi.demo@delni.ly',
                'business_name' => 'المحجوبي للكهرباء والصيانة',
                'bio' => 'كهربائي محترف بخبرة 15 عامًا في تركيب وصيانة الأنظمة الكهربائية السكنية والتجارية في طرابلس وضواحيها.',
                'city' => 'tripoli',
                'category' => 'home-services',
                'subcategories' => ['electrical'],
                'experience_years' => 15,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218916789012',
                'phone' => '+218916789012',
                'reviews' => [5, 4, 5, 5, 4, 5],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
            [
                'user_name' => 'مختار سالم الورشفاني',
                'user_email' => 'mokhtar.demo@delni.ly',
                'business_name' => 'الورشفاني للسباكة',
                'bio' => 'سباك محترف بخبرة 12 عامًا في تركيب وإصلاح شبكات المياه والصرف الصحي للمنازل والمجمعات السكنية.',
                'city' => 'misrata',
                'category' => 'home-services',
                'subcategories' => ['plumbing'],
                'experience_years' => 12,
                'type' => 'individual',
                'provider_type' => 'freelancer',
                'whatsapp' => '+218920123456',
                'phone' => '+218920123456',
                'reviews' => [4, 3, 4],
                'is_homepage_featured' => false,
                'homepage_featured_until' => null,
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

        $profile = Profile::firstOrCreate(
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
                'offers_remote_work' => true,
                'is_complete' => false,
            ],
        );

        $subcategoryIds = collect($data['subcategories'])
            ->map(fn (string $subSlug) => $categoryEntry['subcategories'][$subSlug]->id)
            ->all();
        $profile->subcategories()->syncWithoutDetaching($subcategoryIds);

        if (! $user->hasRole('provider')) {
            $user->assignRole('provider');
        }

        $profile->update([
            'is_complete' => true,
            'provider_access_ends_at' => Carbon::today()->addYear(),
        ]);

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
    }

    private function demoComment(int $rating): string
    {
        $comments = [
            5 => [
                'خدمة ممتازة وجودة عالية، أنصح بالتعامل معهم.',
                'تعامل راقي ومحترف، سأعود بالتأكيد.',
                'أفضل مزود خدمة تعاملت معه، شكرًا جزيلًا.',
                'جودة الشغل فوق التوقعات، التزام بالمواعيد.',
                'نتائج رائعة في وقت قياسي، موصى به بشدة.',
            ],
            4 => [
                'عمل جيد وخدمة محترمة، أنصح بهم.',
                'راضٍ عن الخدمة بشكل عام، هناك مجال للتطوير.',
                'تجربة إيجابية وسأتعامل معهم مجددًا.',
                'جودة جيدة بسعر مناسب.',
            ],
            3 => [
                'خدمة متوسطة، تحتاج إلى تحسين في التواصل.',
                'النتيجة مقبولة لكن التأخير كان مزعجًا.',
                'يحتاج إلى المزيد من الاهتمام بالتفاصيل.',
            ],
        ];

        $pool = $comments[$rating] ?? $comments[4];

        return $pool[array_rand($pool)];
    }
}
