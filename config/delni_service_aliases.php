<?php

/**
 * Service Aliases Configuration
 *
 * Maps user-spoken Arabic service terms to normalized category/subcategory names.
 * Enables the chatbot to understand natural service requests without asking users
 * about internal Delni category structure.
 */

return [
    // HVAC / Air Conditioning
    'hvac' => [
        'aliases' => ['تكييف', 'مكيف', 'فني تكييف', 'تصليح مكيف', 'تركيب مكيف', 'ac', 'صيانة تكييف'],
        'category_slug' => 'hvac-air-conditioning',
        'confidence' => 'high',
    ],

    // Legal Services
    'legal' => [
        'aliases' => ['محامي', 'قانون', 'قضية', 'استشارة قانونية', 'عقد', 'نيابة', 'محاماة'],
        'category_slug' => 'law-legal-services',
        'confidence' => 'high',
    ],

    // Construction
    'construction' => [
        'aliases' => ['مقاول', 'بناء', 'تشطيب', 'خرائط', 'تنفيذ', 'صيانة بيت', 'هندسة بناء'],
        'category_slug' => 'construction-contracting',
        'confidence' => 'high',
    ],

    // Photography
    'photography' => [
        'aliases' => ['مصور', 'تصوير', 'تصوير عرس', 'تصوير منتجات', 'فوتوغرافي', 'فوتو'],
        'category_slug' => 'photography-videography',
        'confidence' => 'high',
    ],

    // Design
    'design' => [
        'aliases' => ['مصمم', 'شعار', 'هوية بصرية', 'ديكور', 'تصميم داخلي', 'تصميم جرافيكس'],
        'category_slug' => 'design-services',
        'confidence' => 'high',
    ],

    // Electrical
    'electrical' => [
        'aliases' => ['كهربائي', 'كهرباء', 'تركيب كهرباء', 'صيانة كهرباء', 'فني كهرباء'],
        'category_slug' => 'electrical-services',
        'confidence' => 'high',
    ],

    // Plumbing
    'plumbing' => [
        'aliases' => ['سباك', 'سباكة', 'تسريب', 'مواسير', 'صيانة سباكة'],
        'category_slug' => 'plumbing-services',
        'confidence' => 'high',
    ],
];
