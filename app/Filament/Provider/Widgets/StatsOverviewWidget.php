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
                Stat::make('حالة الملف الشخصي', 'لم تكمل')
                    ->description('يرجى إنشاء ملف شخصي')
                    ->color('danger'),
            ];
        }

        $completionPercentage = $profile->calculateCompletionPercentage();
        $stats = [
            Stat::make('اكتمال الملف الشخصي', $completionPercentage.'%')
                ->color($completionPercentage === 100 ? 'success' : 'warning'),
        ];

        $ratingAvg = (float) ($profile->stats?->rating_avg ?? 0.0);
        $reviewsCount = $profile->stats?->reviews_count ?? 0;
        $stats[] = Stat::make('التقييم', number_format($ratingAvg, 1))
            ->description(($reviewsCount).' تقييمات')
            ->color($ratingAvg > 0 ? 'info' : 'gray');

        $activeSubscription = $user->activeSubscription;
        if ($activeSubscription) {
            $stats[] = Stat::make('الاشتراك', 'نشط')
                ->description('ينتهي في: '.$activeSubscription->ends_at->format('d/m/Y'))
                ->color('success');
        } else {
            $stats[] = Stat::make('الاشتراك', 'غير نشط')
                ->color('danger');
        }

        $portfolioCount = $profile->portfolioItems()->count();
        $stats[] = Stat::make('الأعمال والمشاريع', $portfolioCount)
            ->description('من أصل 2 أقصى')
            ->color($portfolioCount < 2 ? 'info' : 'success');

        $imagesCount = $profile->portfolioItems()
            ->withCount('images')
            ->get()
            ->sum('images_count');
        $stats[] = Stat::make('صور الأعمال', $imagesCount)
            ->description('من أصل 8 صور أقصى')
            ->color('info');

        $credentialsCount = $profile->credentials()->count();
        $stats[] = Stat::make('بيانات الاعتماد', $credentialsCount)
            ->color('info');

        if ($profile->stats?->is_featured) {
            $stats[] = Stat::make('الحالة', 'مميز ⭐')
                ->color('success');
        }

        return $stats;
    }
}
