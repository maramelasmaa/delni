<?php

declare(strict_types=1);

namespace App\Enums;

enum BannerLinkType: string
{
    case None = 'none';
    case Category = 'category';
    case Provider = 'provider';
    case Url = 'url';

    public function label(): string
    {
        return match ($this) {
            self::None => 'بدون رابط',
            self::Category => 'تصنيف',
            self::Provider => 'مقدم خدمة',
            self::Url => 'رابط خارجي',
        };
    }
}
