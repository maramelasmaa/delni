<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Graphic Design',
                'name_ar' => 'التصميم الجرافيكي',
                'icon' => '🎨',
                'subs' => [
                    ['name' => 'Logo Design',      'name_ar' => 'تصميم شعارات'],
                    ['name' => 'Social Media',     'name_ar' => 'تصميم سوشيال ميديا'],
                    ['name' => 'Print Design',     'name_ar' => 'تصميم مطبوعات'],
                ],
            ],
            [
                'name' => 'Construction & Contracting',
                'name_ar' => 'المقاولات والبناء',
                'icon' => '🏗️',
                'subs' => [
                    ['name' => 'Building',         'name_ar' => 'بناء'],
                    ['name' => 'Plumbing',         'name_ar' => 'سباكة'],
                    ['name' => 'Electrical',       'name_ar' => 'كهرباء'],
                    ['name' => 'Tiling',           'name_ar' => 'بلاط وتشطيب'],
                ],
            ],
            [
                'name' => 'Tech & Software',
                'name_ar' => 'التقنية والبرمجة',
                'icon' => '💻',
                'subs' => [
                    ['name' => 'Web Development',  'name_ar' => 'تطوير مواقع'],
                    ['name' => 'Mobile Apps',      'name_ar' => 'تطبيقات موبايل'],
                    ['name' => 'IT Support',       'name_ar' => 'دعم تقني'],
                    ['name' => 'Networking',       'name_ar' => 'شبكات'],
                ],
            ],
            [
                'name' => 'Photography & Video',
                'name_ar' => 'التصوير والفيديو',
                'icon' => '📸',
                'subs' => [
                    ['name' => 'Wedding Photography', 'name_ar' => 'تصوير أعراس'],
                    ['name' => 'Product Photography', 'name_ar' => 'تصوير منتجات'],
                    ['name' => 'Video Production',    'name_ar' => 'إنتاج فيديو'],
                ],
            ],
            [
                'name' => 'Legal & Accounting',
                'name_ar' => 'القانون والمحاسبة',
                'icon' => '⚖️',
                'subs' => [
                    ['name' => 'Lawyer',           'name_ar' => 'محامي'],
                    ['name' => 'Accountant',       'name_ar' => 'محاسب'],
                    ['name' => 'Tax Consultant',   'name_ar' => 'استشارات ضريبية'],
                ],
            ],
            [
                'name' => 'Auto & Mechanics',
                'name_ar' => 'السيارات والميكانيكا',
                'icon' => '🚗',
                'subs' => [
                    ['name' => 'Car Mechanic',     'name_ar' => 'ميكانيكي سيارات'],
                    ['name' => 'Auto Electrician', 'name_ar' => 'كهربائي سيارات'],
                    ['name' => 'Car Wash',         'name_ar' => 'غسيل سيارات'],
                    ['name' => 'Tires',            'name_ar' => 'إطارات'],
                ],
            ],
            [
                'name' => 'Medical & Health',
                'name_ar' => 'الصحة والطب',
                'icon' => '🏥',
                'subs' => [
                    ['name' => 'Dentist',          'name_ar' => 'طبيب أسنان'],
                    ['name' => 'General Doctor',   'name_ar' => 'طبيب عام'],
                    ['name' => 'Surgeon',          'name_ar' => 'جراح'],
                    ['name' => 'Pharmacy',         'name_ar' => 'صيدلية'],
                    ['name' => 'Physiotherapy',    'name_ar' => 'علاج طبيعي'],
                ],
            ],
            [
                'name' => 'Logistics & Delivery',
                'name_ar' => 'الشحن والتوصيل',
                'icon' => '📦',
                'subs' => [
                    ['name' => 'Courier',          'name_ar' => 'توصيل طرود'],
                    ['name' => 'Moving Services',  'name_ar' => 'نقل أثاث'],
                    ['name' => 'Freight',          'name_ar' => 'شحن بضائع'],
                ],
            ],
            [
                'name' => 'Catering & Events',
                'name_ar' => 'الفعاليات والتموين',
                'icon' => '🍽️',
                'subs' => [
                    ['name' => 'Catering',         'name_ar' => 'تموين'],
                    ['name' => 'Event Planning',   'name_ar' => 'تنظيم فعاليات'],
                    ['name' => 'Decoration',       'name_ar' => 'زينة وديكور'],
                ],
            ],
            [
                'name' => 'Maintenance & Repairs',
                'name_ar' => 'الصيانة والإصلاح',
                'icon' => '🔧',
                'subs' => [
                    ['name' => 'AC Maintenance',   'name_ar' => 'صيانة مكيفات'],
                    ['name' => 'Appliance Repair', 'name_ar' => 'صيانة أجهزة'],
                    ['name' => 'Painting',         'name_ar' => 'دهانات'],
                    ['name' => 'Carpentry',        'name_ar' => 'نجارة'],
                ],
            ],
        ];

        foreach ($categories as $order => $data) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'name_ar' => $data['name_ar'],
                    'icon' => $data['icon'],
                    'sort_order' => $order,
                    'is_active' => true,
                ]
            );

            foreach ($data['subs'] as $subOrder => $sub) {
                Subcategory::firstOrCreate(
                    [
                        'category_id' => $category->id,
                        'slug' => Str::slug($sub['name']),
                    ],
                    [
                        'name' => $sub['name'],
                        'name_ar' => $sub['name_ar'],
                        'sort_order' => $subOrder,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
