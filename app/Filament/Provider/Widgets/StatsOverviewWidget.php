<?php

declare(strict_types=1);

namespace App\Filament\Provider\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $profile = $user->profile;

        if (! $profile) {
            return [
                Stat::make('حالة الملف الشخصي', 'غير متوفر')
                    ->description('الملف الشخصي لم ينشأ بعد')
                    ->color('danger'),
            ];
        }

        $completionPercentage = $profile->calculateCompletionPercentage();
        $stats = [
            Stat::make('اكتمال الملف الشخصي', $completionPercentage.'%')
                ->description($completionPercentage === 100 ? 'مكتمل ✓' : 'قيد الإكمال')
                ->color($completionPercentage === 100 ? 'success' : 'warning'),
        ];

        $ratingAvg = (float) ($profile->stats?->rating_avg ?? 0.0);
        $reviewsCount = $profile->stats?->reviews_count ?? 0;
        $stats[] = Stat::make('متوسط التقييم', number_format($ratingAvg, 1).' ⭐')
            ->description($reviewsCount.' تقييم')
            ->color($ratingAvg >= 4 ? 'success' : ($ratingAvg > 0 ? 'info' : 'gray'));

        $activeSubscription = $user->activeSubscription;
        if ($activeSubscription) {
            $daysLeft = now()->diffInDays($activeSubscription->expires_at);
            $stats[] = Stat::make('حالة الاشتراك', 'نشط ✓')
                ->description('ينتهي في '.$daysLeft.' يوم')
                ->color($daysLeft > 7 ? 'success' : 'warning');
        } else {
            $stats[] = Stat::make('حالة الاشتراك', 'غير نشط')
                ->description('لا يوجد اشتراك فعال')
                ->color('danger');
        }

        $portfolioCount = $profile->portfolioItems()->count();
        $portfolioStatus = $portfolioCount === 0 ? 'لا توجد مشاريع بعد' : "$portfolioCount / 2";
        $stats[] = Stat::make('الأعمال والمشاريع', $portfolioStatus)
            ->description($portfolioCount < 2 ? 'يمكنك إضافة '.max(0, 2 - $portfolioCount).' مشروع إضافي' : 'وصلت الحد الأقصى')
            ->color($portfolioCount === 0 ? 'warning' : ($portfolioCount < 2 ? 'info' : 'success'));

        $imagesCount = $profile->portfolioItems()
            ->withCount('images')
            ->get()
            ->sum('images_count');
        $stats[] = Stat::make('صور الأعمال', "$imagesCount / 8")
            ->description($imagesCount < 8 ? 'يمكنك إضافة '.max(0, 8 - $imagesCount).' صور إضافية' : 'وصلت الحد الأقصى')
            ->color($imagesCount < 4 ? 'warning' : ($imagesCount < 8 ? 'info' : 'success'));

        $credentialsCount = $profile->credentials()->count();
        $stats[] = Stat::make('الشهادات والخبرات', $credentialsCount)
            ->description($credentialsCount === 0 ? 'أضف شهاداتك لجعل ملفك أقوى' : 'معروضة في ملفك')
            ->color($credentialsCount === 0 ? 'warning' : 'info');

        return $stats;
    }
}
