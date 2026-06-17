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
                Stat::make(__('filament.widgets.profile_status'), __('filament.widgets.unavailable'))
                    ->description(__('filament.widgets.profile_not_created'))
                    ->color('danger'),
            ];
        }

        $completionPercentage = $profile->calculateCompletionPercentage();
        $stats = [
            Stat::make(__('filament.widgets.profile_completion'), $completionPercentage.'%')
                ->description($completionPercentage === 100 ? __('filament.widgets.profile_complete') : __('filament.widgets.in_progress'))
                ->color($completionPercentage === 100 ? 'success' : 'warning'),
        ];

        $ratingAvg = (float) ($profile->stats?->rating_avg ?? 0.0);
        $reviewsCount = $profile->stats?->reviews_count ?? 0;
        $stats[] = Stat::make(__('filament.widgets.average_rating'), number_format($ratingAvg, 1).' ⭐')
            ->description($reviewsCount.' '.__('filament.widgets.ratings'))
            ->color($ratingAvg >= 4 ? 'success' : ($ratingAvg > 0 ? 'info' : 'gray'));

        $accessEndsAt = $profile->provider_access_ends_at;
        if ($accessEndsAt && $accessEndsAt->isFuture()) {
            $daysLeft = (int) now()->diffInDays($accessEndsAt);
            $dayLabel = $daysLeft === 1 ? 'يوم' : 'أيام';
            $stats[] = Stat::make(__('filament.widgets.visibility_status'), __('filament.widgets.active_status'))
                ->description(__('filament.widgets.subscription_ends').' '.$daysLeft.' '.$dayLabel)
                ->color($daysLeft > 7 ? 'success' : 'warning');
        } else {
            $stats[] = Stat::make(__('filament.widgets.visibility_status'), __('filament.widgets.hidden'))
                ->description(__('filament.widgets.no_active_visibility'))
                ->color('danger');
        }

        $portfolioCount = $profile->portfolioItems()->count();
        $portfolioStatus = $portfolioCount === 0 ? __('filament.widgets.no_projects_yet') : "$portfolioCount / 2";
        $stats[] = Stat::make(__('filament.widgets.portfolio_and_projects'), $portfolioStatus)
            ->description($portfolioCount < 2
                ? __('filament.widgets.additional_project', ['count' => max(0, 2 - $portfolioCount)])
                : __('filament.widgets.maximum_reached'))
            ->color($portfolioCount === 0 ? 'warning' : ($portfolioCount < 2 ? 'info' : 'success'));

        $imagesCount = $profile->portfolioItems()
            ->withCount('images')
            ->get()
            ->sum('images_count');
        $stats[] = Stat::make(__('filament.widgets.portfolio_images'), "$imagesCount / 8")
            ->description($imagesCount < 8
                ? __('filament.widgets.additional_images', ['count' => max(0, 8 - $imagesCount)])
                : __('filament.widgets.maximum_reached'))
            ->color($imagesCount < 4 ? 'warning' : ($imagesCount < 8 ? 'info' : 'success'));

        $credentialsCount = $profile->credentials()->count();
        $stats[] = Stat::make(__('filament.widgets.credentials_and_experience'), $credentialsCount)
            ->description($credentialsCount === 0 ? __('filament.widgets.add_credentials') : __('filament.widgets.displayed_on_profile'))
            ->color($credentialsCount === 0 ? 'warning' : 'info');

        return $stats;
    }
}
