<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Seeds a realistic batch of demo providers for load/performance testing.
 *
 * All demo accounts use the @demo.delni.test email domain so they can be purged
 * in one shot when you're done (see README / the cleanup note at the bottom):
 *   php artisan tinker --execute "App\Models\User::where('email','like','%@demo.delni.test')->forceDelete();"
 *
 * Provider count is configurable: DEMO_PROVIDERS=500 php artisan db:seed --class=DemoSeeder --force
 */
class DemoSeeder extends Seeder
{
    private const DEMO_DOMAIN = 'demo.delni.test';

    /**
     * Arabic taxonomy keyed by [name_ar, slug, [subcategory_name_ar => slug, ...]].
     * Names intentionally include the keywords the app's search exercises (تصميم، برمجة، تسويق، تصوير...).
     *
     * @var array<int, array{0: string, 1: string, 2: array<string, string>}>
     */
    private const CATEGORIES = [
        ['التصميم الجرافيكي', 'graphic-design', ['تصميم شعارات' => 'logo-design', 'هوية بصرية' => 'branding', 'تصميم سوشيال ميديا' => 'social-media-design', 'تصميم مطبوعات' => 'print-design']],
        ['البرمجة والتطوير', 'programming', ['تطوير مواقع' => 'web-development', 'تطبيقات الجوال' => 'mobile-apps', 'متاجر إلكترونية' => 'ecommerce', 'واجهات برمجية' => 'apis']],
        ['التسويق الرقمي', 'digital-marketing', ['إدارة حسابات' => 'social-management', 'إعلانات ممولة' => 'paid-ads', 'تحسين محركات البحث' => 'seo', 'تسويق بالمحتوى' => 'content-marketing']],
        ['التصوير والإعلام', 'photography', ['تصوير فوتوغرافي' => 'photo', 'تصوير فيديو' => 'video', 'مونتاج' => 'editing', 'تصوير منتجات' => 'product-photo']],
        ['الكتابة والترجمة', 'writing', ['كتابة محتوى' => 'copywriting', 'ترجمة' => 'translation', 'تدقيق لغوي' => 'proofreading']],
        ['التعليم والتدريب', 'education', ['دروس خصوصية' => 'tutoring', 'دورات تدريبية' => 'courses', 'استشارات تعليمية' => 'edu-consulting']],
        ['الاستشارات والأعمال', 'consulting', ['استشارات إدارية' => 'management', 'محاسبة' => 'accounting', 'دراسات جدوى' => 'feasibility']],
        ['الصوت والموسيقى', 'audio', ['تعليق صوتي' => 'voiceover', 'إنتاج موسيقي' => 'music-production', 'هندسة صوت' => 'sound-engineering']],
    ];

    /**
     * Libyan cities: [name_ar, slug, name_en].
     *
     * @var array<int, array{0: string, 1: string, 2: string}>
     */
    private const CITIES = [
        ['طرابلس', 'tripoli', 'Tripoli'],
        ['بنغازي', 'benghazi', 'Benghazi'],
        ['مصراتة', 'misrata', 'Misrata'],
        ['الزاوية', 'zawiya', 'Zawiya'],
        ['سبها', 'sabha', 'Sabha'],
        ['البيضاء', 'bayda', 'Bayda'],
        ['طبرق', 'tobruk', 'Tobruk'],
        ['زليتن', 'zliten', 'Zliten'],
        ['الخمس', 'khoms', 'Khoms'],
        ['درنة', 'derna', 'Derna'],
    ];

    private const BUSINESS_PREFIXES = ['شركة', 'مؤسسة', 'استوديو', 'فريق', 'مكتب'];

    public function run(): void
    {
        $target = max(1, (int) env('DEMO_PROVIDERS', 250));

        Role::findOrCreate('provider', 'web');

        [$categories, $subcategoriesByCategory] = $this->seedTaxonomy();
        $cities = $this->seedCities();
        $clients = $this->seedClients(40);

        $existing = User::where('email', 'like', '%@'.self::DEMO_DOMAIN)
            ->whereHas('roles', fn ($q) => $q->where('name', 'provider'))
            ->count();

        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) {
            $this->command?->info("Demo providers already at target ({$existing}). Nothing to create.");

            return;
        }

        $this->command?->info("Seeding {$toCreate} demo providers (target {$target}, existing {$existing})...");
        $bar = $this->command?->getOutput()->createProgressBar($toCreate);
        $bar?->start();

        for ($i = $existing; $i < $existing + $toCreate; $i++) {
            $categoryIndex = array_rand(self::CATEGORIES);
            $category = $categories[$categoryIndex];
            $subcategories = $subcategoriesByCategory[$category->id];

            $this->createProvider($i, $category, $subcategories, $cities, $clients);

            $bar?->advance();
        }

        $bar?->finish();
        $this->command?->newLine();
        $this->command?->info("Done. Total demo providers: {$target}.");
    }

    /**
     * @return array{0: array<int, Category>, 1: array<int, Collection<int, Subcategory>>}
     */
    private function seedTaxonomy(): array
    {
        $categories = [];
        $subcategoriesByCategory = [];

        foreach (self::CATEGORIES as $sort => [$nameAr, $slug, $subs]) {
            $category = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst(str_replace('-', ' ', $slug)), 'name_ar' => $nameAr, 'is_active' => true, 'sort_order' => $sort],
            );

            foreach (array_values($subs) as $subSort => $subSlug) {
                $subNameAr = array_search($subSlug, $subs, true);
                Subcategory::firstOrCreate(
                    ['slug' => $subSlug],
                    [
                        'category_id' => $category->id,
                        'name' => ucfirst(str_replace('-', ' ', $subSlug)),
                        'name_ar' => $subNameAr,
                        'search_name' => $subNameAr,
                        'is_active' => true,
                        'sort_order' => $subSort,
                    ],
                );
            }

            $categories[] = $category;
            $subcategoriesByCategory[$category->id] = $category->subcategories()->get();
        }

        return [$categories, $subcategoriesByCategory];
    }

    /**
     * @return array<int, City>
     */
    private function seedCities(): array
    {
        return collect(self::CITIES)
            ->map(fn (array $city): City => City::firstOrCreate(
                ['slug' => $city[1]],
                ['name' => $city[2], 'name_ar' => $city[0], 'is_active' => true],
            ))
            ->all();
    }

    /**
     * A reusable pool of client accounts to author reviews.
     *
     * @return Collection<int, User>
     */
    private function seedClients(int $count): Collection
    {
        $clients = new Collection;

        for ($i = 0; $i < $count; $i++) {
            $clients->push(User::firstOrCreate(
                ['email' => "client{$i}@".self::DEMO_DOMAIN],
                ['name' => fake()->name(), 'email_verified_at' => now(), 'password' => bcrypt('password'), 'is_active' => true, 'is_suspended' => false],
            ));
        }

        return $clients;
    }

    /**
     * @param  Collection<int, Subcategory>  $subcategories
     * @param  array<int, City>  $cities
     * @param  Collection<int, User>  $clients
     */
    private function createProvider(int $index, Category $category, Collection $subcategories, array $cities, Collection $clients): void
    {
        $city = $cities[array_rand($cities)];
        $prefix = self::BUSINESS_PREFIXES[array_rand(self::BUSINESS_PREFIXES)];
        $businessName = "{$prefix} ".fake()->lastName().' - '.$category->name_ar;

        $user = User::factory()->create([
            'name' => fake()->name(),
            'email' => "provider{$index}@".self::DEMO_DOMAIN,
        ]);
        $user->assignRole('provider');

        $profile = Profile::factory()
            ->complete()
            ->withAccess()
            ->withStats()
            ->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'city_id' => $city->id,
                'business_name' => $businessName,
                'bio' => "نقدم خدمات {$category->name_ar} باحترافية عالية في {$city->name_ar}. خبرة واسعة ونتائج تليق بعملائنا.",
                'offers_remote_work' => fake()->boolean(40),
            ]);

        // Attach 1-3 subcategories from this provider's category.
        $profile->subcategories()->attach(
            $subcategories->random(min($subcategories->count(), random_int(1, 3)))->pluck('id')->all(),
        );

        // 0-8 approved reviews from distinct clients (the ReviewObserver recalculates stats).
        $reviewCount = random_int(0, 8);
        if ($reviewCount > 0) {
            foreach ($clients->random(min($reviewCount, $clients->count())) as $client) {
                Review::create([
                    'profile_id' => $profile->id,
                    'user_id' => $client->id,
                    'rating' => random_int(3, 5),
                    'comment' => fake()->sentence(),
                    'status' => ReviewStatus::APPROVED,
                ]);
            }
        }
    }
}
