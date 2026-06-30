<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DemoProvidersSeeder extends Seeder
{
    private const PASSWORD = 'Password1234@';

    /**
     * @var array<int, array{slug: string, name: string, name_ar: string}>
     */
    private array $cities = [
        ['slug' => 'tripoli', 'name' => 'Tripoli', 'name_ar' => 'طرابلس'],
        ['slug' => 'benghazi', 'name' => 'Benghazi', 'name_ar' => 'بنغازي'],
        ['slug' => 'misrata', 'name' => 'Misrata', 'name_ar' => 'مصراتة'],
        ['slug' => 'zawiya', 'name' => 'Zawiya', 'name_ar' => 'الزاوية'],
        ['slug' => 'sabha', 'name' => 'Sabha', 'name_ar' => 'سبها'],
        ['slug' => 'bayda', 'name' => 'Bayda', 'name_ar' => 'البيضاء'],
    ];

    /**
     * @var array<int, array{name_ar: string, name: string, email: string, category: string, subcategory: string, city: string, phone: string, rating: float, reviews: int}>
     */
    private array $providers = [
        ['name_ar' => 'مركز مدى الطبي', 'name' => 'Mada Medical Center', 'email' => 'info@madacenter.ly', 'category' => 'health', 'subcategory' => 'medical-centers', 'city' => 'tripoli', 'phone' => '+218910100001', 'rating' => 4.8, 'reviews' => 41],
        ['name_ar' => 'عيادات لين', 'name' => 'Leen Clinics', 'email' => 'hello@leenclinic.ly', 'category' => 'health', 'subcategory' => 'clinics', 'city' => 'benghazi', 'phone' => '+218910100002', 'rating' => 4.7, 'reviews' => 36],
        ['name_ar' => 'صيدلية مدار', 'name' => 'Madar Pharmacy', 'email' => 'info@madarpharmacy.ly', 'category' => 'health', 'subcategory' => 'pharmacies', 'city' => 'misrata', 'phone' => '+218910100003', 'rating' => 4.6, 'reviews' => 28],
        ['name_ar' => 'مختبر بيان', 'name' => 'Bayan Laboratory', 'email' => 'contact@bayanlab.ly', 'category' => 'health', 'subcategory' => 'laboratories', 'city' => 'tripoli', 'phone' => '+218910100004', 'rating' => 4.9, 'reviews' => 52],
        ['name_ar' => 'مطعم دارنا', 'name' => 'Darna Restaurant', 'email' => 'hello@darna.ly', 'category' => 'restaurants-cafes', 'subcategory' => 'restaurants', 'city' => 'tripoli', 'phone' => '+218910100005', 'rating' => 4.7, 'reviews' => 67],
        ['name_ar' => 'مقهى رواق', 'name' => 'Riwaq Cafe', 'email' => 'info@riwaq.ly', 'category' => 'restaurants-cafes', 'subcategory' => 'coffee-shops', 'city' => 'benghazi', 'phone' => '+218910100006', 'rating' => 4.6, 'reviews' => 44],
        ['name_ar' => 'مخبز سنابل', 'name' => 'Sanabel Bakery', 'email' => 'contact@sanabel.ly', 'category' => 'restaurants-cafes', 'subcategory' => 'bakeries', 'city' => 'misrata', 'phone' => '+218910100007', 'rating' => 4.8, 'reviews' => 58],
        ['name_ar' => 'حلويات لوزة', 'name' => 'Lawza Sweets', 'email' => 'hello@lawza.ly', 'category' => 'restaurants-cafes', 'subcategory' => 'sweets', 'city' => 'zawiya', 'phone' => '+218910100008', 'rating' => 4.7, 'reviews' => 39],
        ['name_ar' => 'بيت العطور', 'name' => 'Beit Al Otoor', 'email' => 'info@beitalotour.ly', 'category' => 'shopping', 'subcategory' => 'perfumes', 'city' => 'tripoli', 'phone' => '+218910100009', 'rating' => 4.5, 'reviews' => 31],
        ['name_ar' => 'مفروشات بيت', 'name' => 'Beit Furniture', 'email' => 'contact@beitfurniture.ly', 'category' => 'shopping', 'subcategory' => 'furniture', 'city' => 'benghazi', 'phone' => '+218910100010', 'rating' => 4.6, 'reviews' => 34],
        ['name_ar' => 'مكتبة ورق', 'name' => 'Waraq Bookstore', 'email' => 'hello@waraq.ly', 'category' => 'shopping', 'subcategory' => 'gifts', 'city' => 'misrata', 'phone' => '+218910100011', 'rating' => 4.8, 'reviews' => 25],
        ['name_ar' => 'متجر خزانة', 'name' => 'Khazana Store', 'email' => 'info@khazana.ly', 'category' => 'shopping', 'subcategory' => 'clothing', 'city' => 'tripoli', 'phone' => '+218910100012', 'rating' => 4.4, 'reviews' => 29],
        ['name_ar' => 'مركز المسار للسيارات', 'name' => 'Al Masar Auto Center', 'email' => 'info@almasarauto.ly', 'category' => 'cars', 'subcategory' => 'car-dealerships', 'city' => 'tripoli', 'phone' => '+218910100013', 'rating' => 4.6, 'reviews' => 42],
        ['name_ar' => 'ورشة المحرك', 'name' => 'Al Moharek Workshop', 'email' => 'hello@almoharek.ly', 'category' => 'cars', 'subcategory' => 'mechanic-workshops', 'city' => 'benghazi', 'phone' => '+218910100014', 'rating' => 4.7, 'reviews' => 37],
        ['name_ar' => 'إطارات الطريق', 'name' => 'Tareeq Tires', 'email' => 'contact@tareeqtires.ly', 'category' => 'cars', 'subcategory' => 'tires', 'city' => 'misrata', 'phone' => '+218910100015', 'rating' => 4.5, 'reviews' => 24],
        ['name_ar' => 'معرض أوتو لاين', 'name' => 'Auto Line Showroom', 'email' => 'info@autoline.ly', 'category' => 'cars', 'subcategory' => 'car-dealerships', 'city' => 'zawiya', 'phone' => '+218910100016', 'rating' => 4.6, 'reviews' => 33],
        ['name_ar' => 'دار الصيانة', 'name' => 'Dar Maintenance', 'email' => 'info@darmaintenance.ly', 'category' => 'home-maintenance', 'subcategory' => 'electricity', 'city' => 'tripoli', 'phone' => '+218910100017', 'rating' => 4.7, 'reviews' => 40],
        ['name_ar' => 'تكييف مدار', 'name' => 'Madar AC', 'email' => 'hello@madarac.ly', 'category' => 'home-maintenance', 'subcategory' => 'air-conditioning', 'city' => 'benghazi', 'phone' => '+218910100018', 'rating' => 4.8, 'reviews' => 45],
        ['name_ar' => 'سباكة بيتك', 'name' => 'Beitak Plumbing', 'email' => 'contact@beitak.ly', 'category' => 'home-maintenance', 'subcategory' => 'plumbing', 'city' => 'misrata', 'phone' => '+218910100019', 'rating' => 4.6, 'reviews' => 27],
        ['name_ar' => 'لمعة للتنظيف', 'name' => 'Lamaa Cleaning', 'email' => 'info@lamaa.ly', 'category' => 'home-maintenance', 'subcategory' => 'cleaning', 'city' => 'zawiya', 'phone' => '+218910100020', 'rating' => 4.5, 'reviews' => 30],
        ['name_ar' => 'شركة أساس للمقاولات', 'name' => 'Asas Contracting', 'email' => 'info@asas.ly', 'category' => 'construction-engineering', 'subcategory' => 'contracting-companies', 'city' => 'tripoli', 'phone' => '+218910100021', 'rating' => 4.7, 'reviews' => 34],
        ['name_ar' => 'مكتب عمران الهندسي', 'name' => 'Omran Engineering Office', 'email' => 'hello@omran.ly', 'category' => 'construction-engineering', 'subcategory' => 'engineering-offices', 'city' => 'benghazi', 'phone' => '+218910100022', 'rating' => 4.8, 'reviews' => 38],
        ['name_ar' => 'رخام حجر', 'name' => 'Hajar Marble', 'email' => 'contact@hajar.ly', 'category' => 'construction-engineering', 'subcategory' => 'marble-granite', 'city' => 'misrata', 'phone' => '+218910100023', 'rating' => 4.5, 'reviews' => 21],
        ['name_ar' => 'ألمنيوم دار', 'name' => 'Dar Aluminum', 'email' => 'info@daralu.ly', 'category' => 'construction-engineering', 'subcategory' => 'aluminum', 'city' => 'zawiya', 'phone' => '+218910100024', 'rating' => 4.6, 'reviews' => 26],
        ['name_ar' => 'مدار التقنية', 'name' => 'Madar Technology', 'email' => 'info@madartech.ly', 'category' => 'technology', 'subcategory' => 'software-companies', 'city' => 'tripoli', 'phone' => '+218910100025', 'rating' => 4.9, 'reviews' => 46],
        ['name_ar' => 'بيت البرمجة', 'name' => 'Code House', 'email' => 'hello@codehouse.ly', 'category' => 'technology', 'subcategory' => 'app-design', 'city' => 'benghazi', 'phone' => '+218910100026', 'rating' => 4.8, 'reviews' => 41],
        ['name_ar' => 'حلول رقمية', 'name' => 'Digital Solutions', 'email' => 'contact@digital.ly', 'category' => 'technology', 'subcategory' => 'website-design', 'city' => 'misrata', 'phone' => '+218910100027', 'rating' => 4.7, 'reviews' => 35],
        ['name_ar' => 'شبكة بلس', 'name' => 'Shabaka Plus', 'email' => 'info@shabakaplus.ly', 'category' => 'technology', 'subcategory' => 'surveillance-cameras', 'city' => 'tripoli', 'phone' => '+218910100028', 'rating' => 4.6, 'reviews' => 29],
        ['name_ar' => 'أكاديمية مدار', 'name' => 'Madar Academy', 'email' => 'info@madaracademy.ly', 'category' => 'education', 'subcategory' => 'training-centers', 'city' => 'tripoli', 'phone' => '+218910100029', 'rating' => 4.8, 'reviews' => 48],
        ['name_ar' => 'مركز بيان', 'name' => 'Bayan Education Center', 'email' => 'hello@bayanedu.ly', 'category' => 'education', 'subcategory' => 'language-centers', 'city' => 'benghazi', 'phone' => '+218910100030', 'rating' => 4.7, 'reviews' => 33],
        ['name_ar' => 'حضانة براعم', 'name' => 'Baraem Nursery', 'email' => 'contact@baraem.ly', 'category' => 'education', 'subcategory' => 'nurseries', 'city' => 'misrata', 'phone' => '+218910100031', 'rating' => 4.6, 'reviews' => 24],
        ['name_ar' => 'معهد مهارة', 'name' => 'Mahara Institute', 'email' => 'info@mahara.ly', 'category' => 'education', 'subcategory' => 'institutes', 'city' => 'zawiya', 'phone' => '+218910100032', 'rating' => 4.8, 'reviews' => 37],
        ['name_ar' => 'صالون لمسة', 'name' => 'Lamsa Salon', 'email' => 'hello@lamsa.ly', 'category' => 'beauty-care', 'subcategory' => 'women-salons', 'city' => 'tripoli', 'phone' => '+218910100033', 'rating' => 4.7, 'reviews' => 54],
        ['name_ar' => 'مركز نضارة', 'name' => 'Nadara Center', 'email' => 'info@nadara.ly', 'category' => 'beauty-care', 'subcategory' => 'beauty-centers', 'city' => 'benghazi', 'phone' => '+218910100034', 'rating' => 4.8, 'reviews' => 43],
        ['name_ar' => 'دار الجمال', 'name' => 'Dar Beauty', 'email' => 'contact@darbeauty.ly', 'category' => 'beauty-care', 'subcategory' => 'skin-care', 'city' => 'misrata', 'phone' => '+218910100035', 'rating' => 4.6, 'reviews' => 32],
        ['name_ar' => 'سبا سكون', 'name' => 'Sukun Spa', 'email' => 'hello@sukun.ly', 'category' => 'beauty-care', 'subcategory' => 'spa', 'city' => 'zawiya', 'phone' => '+218910100036', 'rating' => 4.9, 'reviews' => 39],
        ['name_ar' => 'مكتب دار العقاري', 'name' => 'Dar Real Estate Office', 'email' => 'info@darrealestate.ly', 'category' => 'real-estate', 'subcategory' => 'real-estate-offices', 'city' => 'tripoli', 'phone' => '+218910100037', 'rating' => 4.6, 'reviews' => 28],
        ['name_ar' => 'عقارات المدار', 'name' => 'Madar Estate', 'email' => 'hello@madarestate.ly', 'category' => 'real-estate', 'subcategory' => 'real-estate-marketing', 'city' => 'benghazi', 'phone' => '+218910100038', 'rating' => 4.7, 'reviews' => 35],
        ['name_ar' => 'سكن للعقارات', 'name' => 'Sakan Real Estate', 'email' => 'contact@sakan.ly', 'category' => 'real-estate', 'subcategory' => 'rentals', 'city' => 'misrata', 'phone' => '+218910100039', 'rating' => 4.5, 'reviews' => 22],
        ['name_ar' => 'أملاك المدينة', 'name' => 'Al Madina Properties', 'email' => 'info@almadinaestate.ly', 'category' => 'real-estate', 'subcategory' => 'property-management', 'city' => 'tripoli', 'phone' => '+218910100040', 'rating' => 4.8, 'reviews' => 31],
        ['name_ar' => 'قاعة ليالي', 'name' => 'Layali Hall', 'email' => 'info@layali.ly', 'category' => 'events', 'subcategory' => 'wedding-halls', 'city' => 'tripoli', 'phone' => '+218910100041', 'rating' => 4.7, 'reviews' => 49],
        ['name_ar' => 'استوديو لقطة', 'name' => 'Laqta Studio', 'email' => 'hello@laqta.ly', 'category' => 'events', 'subcategory' => 'photography', 'city' => 'benghazi', 'phone' => '+218910100042', 'rating' => 4.8, 'reviews' => 45],
        ['name_ar' => 'ديكور رُقي', 'name' => 'Roqi Decor', 'email' => 'contact@roqi.ly', 'category' => 'events', 'subcategory' => 'event-decoration', 'city' => 'misrata', 'phone' => '+218910100043', 'rating' => 4.6, 'reviews' => 27],
        ['name_ar' => 'هدايا لمسة', 'name' => 'Lamsa Gifts', 'email' => 'info@giftslamsa.ly', 'category' => 'events', 'subcategory' => 'gifts-wrapping', 'city' => 'zawiya', 'phone' => '+218910100044', 'rating' => 4.7, 'reviews' => 36],
        ['name_ar' => 'شركة المدار للشحن', 'name' => 'Madar Cargo', 'email' => 'info@madarcargo.ly', 'category' => 'business-services', 'subcategory' => 'shipping-companies', 'city' => 'tripoli', 'phone' => '+218910100045', 'rating' => 4.6, 'reviews' => 40],
        ['name_ar' => 'شركة بُعد للتسويق', 'name' => 'Boad Marketing', 'email' => 'hello@boad.ly', 'category' => 'business-services', 'subcategory' => 'marketing-companies', 'city' => 'benghazi', 'phone' => '+218910100046', 'rating' => 4.7, 'reviews' => 34],
        ['name_ar' => 'مكتب البيان للمحاسبة', 'name' => 'Al Bayan Accounting Office', 'email' => 'contact@bayanaccounting.ly', 'category' => 'business-services', 'subcategory' => 'marketing-companies', 'city' => 'misrata', 'phone' => '+218910100047', 'rating' => 4.5, 'reviews' => 23],
        ['name_ar' => 'شركة أثر للدعاية', 'name' => 'Athar Media', 'email' => 'info@atharmedia.ly', 'category' => 'business-services', 'subcategory' => 'advertising', 'city' => 'tripoli', 'phone' => '+218910100048', 'rating' => 4.8, 'reviews' => 38],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            RolesAndPermissionsSeeder::class,
            ProviderTypesSeeder::class,
            ServiceTaxonomySeeder::class,
        ]);

        Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'web']);

        $cities = $this->seedCities();

        foreach ($this->providers as $index => $providerData) {
            $category = Category::query()->where('slug', $providerData['category'])->firstOrFail();
            $subcategory = Subcategory::query()
                ->where('slug', $providerData['subcategory'])
                ->where('category_id', $category->id)
                ->first()
                ?? $category->subcategories()->firstOrFail();
            $city = $cities[$providerData['city']] ?? $cities['tripoli'];
            $slug = Str::slug($providerData['name']);

            $user = User::withTrashed()->updateOrCreate(
                ['email' => $providerData['email']],
                [
                    'name' => $providerData['name_ar'],
                    'phone' => $providerData['phone'],
                    'password' => Hash::make(self::PASSWORD),
                    'is_active' => true,
                    'is_suspended' => false,
                    'security_flagged' => false,
                    'email_verified_at' => now(),
                ],
            );

            if ($user->trashed()) {
                $user->restore();
            }

            $user->syncRoles(['provider']);

            $profile = Profile::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $providerData['name_ar'],
                    'type' => 'business',
                    'provider_type' => 'company',
                    'bio' => $this->bio($providerData['name_ar'], $category->name_ar, $city->name_ar),
                    'slug' => $slug,
                    'offers_remote_work' => in_array($providerData['category'], ['technology', 'business-services', 'education'], true),
                    'map_url' => $this->mapUrl($providerData['name_ar'], $city->name_ar),
                    'service_area_note' => 'يخدم العملاء في '.$city->name_ar.' والمناطق القريبة.',
                    'city_id' => $city->id,
                    'category_id' => $category->id,
                    'phone' => $providerData['phone'],
                    'whatsapp' => $providerData['phone'],
                    'experience_years' => 3 + ($index % 8),
                    'website' => 'https://'.$this->domainFromEmail($providerData['email']),
                    'instagram_handle' => Str::slug($providerData['name']).'.ly',
                    'facebook_slug' => Str::slug($providerData['name']),
                    'linkedin_slug' => 'company/'.Str::slug($providerData['name']),
                    'github_username' => $providerData['category'] === 'technology' ? Str::slug($providerData['name']) : null,
                    'logo' => null,
                    'cover_image' => null,
                    'is_complete' => true,
                    'provider_access_ends_at' => now()->addYear(),
                ],
            );

            if ($profile->trashed()) {
                $profile->restore();
            }

            $profile->subcategories()->sync([$subcategory->id]);
            $this->seedProviderLinks($profile, $providerData);
            $this->seedPortfolioItems($profile, $providerData, $category, $city);

            ProfileStats::query()->updateOrCreate(
                ['profile_id' => $profile->id],
                [
                    'rating_avg' => $providerData['rating'],
                    'reviews_count' => $providerData['reviews'],
                    'is_top_rated' => $providerData['rating'] >= 4.8,
                    'is_homepage_featured' => $index < 8,
                    'homepage_featured_until' => $index < 8 ? now()->addMonths(6)->toDateString() : null,
                    'is_top_search' => $index < 12,
                    'top_search_until' => $index < 12 ? now()->addMonths(6)->toDateString() : null,
                    'is_top_category' => $index % 4 === 0,
                    'top_category_until' => $index % 4 === 0 ? now()->addMonths(6)->toDateString() : null,
                    'is_top_subcategory' => $index % 4 === 1,
                    'top_subcategory_until' => $index % 4 === 1 ? now()->addMonths(6)->toDateString() : null,
                ],
            );
        }

        $this->command?->info('Seeded '.count($this->providers).' demo providers. Password for every provider: '.self::PASSWORD);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<string, City>
     */
    private function seedCities(): array
    {
        $cities = [];

        foreach ($this->cities as $index => $cityData) {
            $city = City::withTrashed()->updateOrCreate(
                ['slug' => $cityData['slug']],
                [
                    'name' => $cityData['name'],
                    'name_ar' => $cityData['name_ar'],
                    'icon' => 'heroicon-o-map-pin',
                    'is_active' => true,
                ],
            );

            if ($city->trashed()) {
                $city->restore();
            }

            $city->forceFill(['sort_order' => ($index + 1) * 10])->save();

            $cities[$cityData['slug']] = $city;
        }

        return $cities;
    }

    private function bio(string $businessName, string $categoryName, string $cityName): string
    {
        return $businessName.' مزود خدمات موثوق ضمن '.$categoryName.' في '.$cityName.'، يقدم تجربة واضحة للعملاء مع بيانات تواصل كاملة، وصف خدمات مفيد، ومشاريع نموذجية لاختبار البحث والفلاتر داخل تطبيق دلني.';
    }

    private function domainFromEmail(string $email): string
    {
        return Str::after($email, '@');
    }

    private function mapUrl(string $businessName, string $cityName): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($businessName.' '.$cityName.' ليبيا');
    }

    /**
     * @param array{name_ar: string, name: string, email: string} $providerData
     */
    private function seedProviderLinks(Profile $profile, array $providerData): void
    {
        $domain = $this->domainFromEmail($providerData['email']);
        $handle = Str::slug($providerData['name']);

        $links = [
            ['type' => 'website', 'label' => 'الموقع الرسمي', 'url' => 'https://'.$domain],
            ['type' => 'instagram', 'label' => 'إنستغرام', 'url' => 'https://instagram.com/'.$handle.'.ly'],
            ['type' => 'facebook', 'label' => 'فيسبوك', 'url' => 'https://facebook.com/'.$handle],
        ];

        foreach ($links as $index => $link) {
            $profile->links()->updateOrCreate(
                ['type' => $link['type']],
                [
                    'label' => $link['label'],
                    'url' => $link['url'],
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @param array{name_ar: string, name: string, category: string} $providerData
     */
    private function seedPortfolioItems(Profile $profile, array $providerData, Category $category, City $city): void
    {
        foreach ($this->portfolioTemplates($providerData['category']) as $index => $template) {
            PortfolioItem::query()->updateOrCreate(
                [
                    'profile_id' => $profile->id,
                    'sort_order' => ($index + 1) * 10,
                ],
                [
                    'title' => $providerData['name_ar'].' - '.$template['title'],
                    'short_description' => $template['short'],
                    'description' => $providerData['name_ar'].' نفذ '.$template['description'].' ضمن '.$category->name_ar.' في '.$city->name_ar.'، مع ترك مساحة الصور فارغة لإضافة صور حقيقية لاحقا.',
                    'main_url' => 'https://'.$this->domainFromEmail($providerData['email']),
                    'link' => 'https://'.$this->domainFromEmail($providerData['email']),
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array{title: string, short: string, description: string}>
     */
    private function portfolioTemplates(string $categorySlug): array
    {
        return match ($categorySlug) {
            'health' => [
                ['title' => 'تنظيم تجربة المراجعين', 'short' => 'تسهيل الحجز والاستقبال والمتابعة.', 'description' => 'مشروعا لتحسين استقبال المراجعين وتنظيم المواعيد وتقليل وقت الانتظار'],
                ['title' => 'خطة متابعة الحالات', 'short' => 'متابعة أوضح بعد الزيارة.', 'description' => 'مشروعا لمتابعة العملاء بعد الخدمة وتوثيق الملاحظات الأساسية'],
            ],
            'restaurants-cafes' => [
                ['title' => 'قائمة موسمية محسنة', 'short' => 'تجربة طلب أوضح وأسهل.', 'description' => 'مشروعا لتحديث القائمة وتنظيم الأصناف الأكثر طلبا'],
                ['title' => 'تجهيز طلبات المناسبات', 'short' => 'طلبات جماعية بتنسيق أفضل.', 'description' => 'مشروعا لتجهيز الطلبات الكبيرة وتنظيم مواعيد التسليم'],
            ],
            'shopping' => [
                ['title' => 'تنسيق واجهة العرض', 'short' => 'عرض منتجات أكثر وضوحا.', 'description' => 'مشروعا لترتيب المنتجات والعروض حسب احتياج العملاء'],
                ['title' => 'خدمة طلب وتسليم', 'short' => 'تواصل أسرع حول توفر المنتجات.', 'description' => 'مشروعا لتحسين متابعة الطلبات والتسليم داخل المدينة'],
            ],
            'cars' => [
                ['title' => 'فحص سريع قبل الخدمة', 'short' => 'تقييم أوضح لحالة السيارة.', 'description' => 'مشروعا لتنظيم فحص السيارات وتقديم ملاحظات واضحة للعميل'],
                ['title' => 'متابعة الصيانة الدورية', 'short' => 'تذكير وخطة صيانة مبسطة.', 'description' => 'مشروعا لتوثيق الصيانة الدورية وتحديد مواعيد المتابعة'],
            ],
            'home-maintenance' => [
                ['title' => 'زيارات صيانة منزلية', 'short' => 'تنظيم مواعيد الزيارة والفحص.', 'description' => 'مشروعا لترتيب زيارات الصيانة وتحديد الأعمال المطلوبة قبل الوصول'],
                ['title' => 'خطة إصلاح عاجل', 'short' => 'استجابة أسرع للأعطال.', 'description' => 'مشروعا لتحسين التعامل مع البلاغات العاجلة ومتابعة نتائج الإصلاح'],
            ],
            'construction-engineering' => [
                ['title' => 'إدارة تنفيذ موقع', 'short' => 'تنسيق أوضح بين العميل والفريق.', 'description' => 'مشروعا لتنظيم مراحل التنفيذ ومتابعة تقدم الأعمال'],
                ['title' => 'عرض فني وتقديري', 'short' => 'نطاق عمل وتكلفة أكثر وضوحا.', 'description' => 'مشروعا لإعداد عروض فنية مفهومة تشمل المواد والمدة والتكلفة'],
            ],
            'technology' => [
                ['title' => 'تحسين حضور رقمي', 'short' => 'موقع أو تطبيق أكثر جاهزية.', 'description' => 'مشروعا لتحسين واجهة رقمية وتجهيزها للاستخدام اليومي'],
                ['title' => 'دعم وتشغيل تقني', 'short' => 'متابعة تقنية منظمة.', 'description' => 'مشروعا لتنظيم الدعم الفني ومتابعة البلاغات وحالات الصيانة'],
            ],
            'education' => [
                ['title' => 'برنامج تدريبي منظم', 'short' => 'محتوى واضح ومواعيد ثابتة.', 'description' => 'مشروعا لتنسيق برنامج تعليمي مناسب للطلاب والمتدربين'],
                ['title' => 'متابعة تقدم الطلاب', 'short' => 'قياس مبسط للمستوى.', 'description' => 'مشروعا لمتابعة الحضور والتقدم وتقديم ملاحظات دورية'],
            ],
            'beauty-care' => [
                ['title' => 'تنظيم حجوزات العناية', 'short' => 'مواعيد وخدمات أكثر وضوحا.', 'description' => 'مشروعا لتحسين تجربة الحجز وتجهيز الخدمة قبل الموعد'],
                ['title' => 'باقات عناية موسمية', 'short' => 'خدمات مجمعة بشكل مرتب.', 'description' => 'مشروعا لإعداد باقات عناية تناسب المواسم واحتياجات العملاء'],
            ],
            'real-estate' => [
                ['title' => 'عرض عقارات منظم', 'short' => 'معلومات أوضح للمشتري والمستأجر.', 'description' => 'مشروعا لترتيب بيانات العقارات وتسهيل المقارنة بينها'],
                ['title' => 'متابعة طلبات العملاء', 'short' => 'ربط أسرع بين الطلب والعرض.', 'description' => 'مشروعا لمتابعة طلبات الشراء والإيجار واقتراح خيارات مناسبة'],
            ],
            'events' => [
                ['title' => 'تنسيق مناسبة كاملة', 'short' => 'جدول وتجهيزات أوضح.', 'description' => 'مشروعا لتنظيم تفاصيل المناسبة من الحجز حتى يوم التنفيذ'],
                ['title' => 'باقة تصوير وتجهيز', 'short' => 'خدمة مناسبة جاهزة للإضافة بالصور.', 'description' => 'مشروعا لتجهيز عناصر المناسبة وتوثيق مراحل العمل'],
            ],
            'business-services' => [
                ['title' => 'تنظيم خدمة للشركات', 'short' => 'سير عمل أوضح للعملاء.', 'description' => 'مشروعا لتحسين استقبال طلبات الشركات ومتابعتها حتى الإغلاق'],
                ['title' => 'حملة تشغيل شهرية', 'short' => 'خطة عمل قابلة للقياس.', 'description' => 'مشروعا لإدارة حملة أو خدمة شهرية بنتائج واضحة'],
            ],
            default => [
                ['title' => 'مشروع خدمة مميز', 'short' => 'نموذج عمل واضح.', 'description' => 'مشروعا لتقديم خدمة منظمة وقابلة للعرض'],
                ['title' => 'تحسين تجربة العملاء', 'short' => 'تواصل ومتابعة أفضل.', 'description' => 'مشروعا لتحسين التواصل والمتابعة مع العملاء'],
            ],
        };
    }
}
