<?php

declare(strict_types=1);

namespace App\Filament\Provider\Pages;

use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Dashboard extends Page
{
    protected static string $routePath = '/dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'لوحة التحكم';

    protected static bool $shouldRegisterNavigation = false;

    public function getHeading(): string
    {
        return __('Dashboard');
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $profile = $user->profile ?? null;

        if (! $profile) {
            return [
                Stat::make('حالة الملف الشخصي', 'لم تكمل')
                    ->description('يرجى إنشاء ملف شخصي')
                    ->color('danger'),
            ];
        }

        $stats = [
            Stat::make('اكتمال الملف الشخصي', $profile->calculateCompletionPercentage().'%')
                ->color($profile->calculateCompletionPercentage() === 100 ? 'success' : 'warning'),
        ];

        if ($profile->stats) {
            $stats[] = Stat::make('التقييم', $profile->stats->rating_avg ?? '0.0')
                ->description(($profile->stats->reviews_count ?? 0).' تقييمات')
                ->color('info');
        } else {
            $stats[] = Stat::make('التقييم', '0.0')
                ->description('0 تقييمات')
                ->color('gray');
        }

        $activeSubscription = $user->activeSubscription ?? null;
        if ($activeSubscription) {
            $stats[] = Stat::make('الاشتراك', 'نشط')
                ->description('ينتهي في: '.$activeSubscription->ends_at->format('d/m/Y'))
                ->color('success');
        } else {
            $stats[] = Stat::make('الاشتراك', 'غير نشط')
                ->color('danger');
        }

        $stats[] = Stat::make('المشاريع', $profile->portfolioItems()->count())
            ->description('من أصل 2 أقصى')
            ->color('info');

        $stats[] = Stat::make('بيانات الاعتماد', $profile->credentials()->count())
            ->color('info');

        if ($profile->stats?->is_featured) {
            $stats[] = Stat::make('الحالة', 'مميز')
                ->color('success');
        }

        return $stats;
    }

    public function getViewData(): array
    {
        $user = auth()->user();
        $profile = $user->profile ?? null;

        return [
            'profile' => $profile,
            'completionPercentage' => $profile?->calculateCompletionPercentage() ?? 0,
            'hasActiveSubscription' => $user->activeSubscription !== null,
            'stats' => $this->getStats(),
        ];
    }
}
