<?php

declare(strict_types=1);

namespace App\Filament\Provider\Pages;

use App\Filament\Provider\Widgets\StatsOverviewWidget;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected string $view = 'filament.provider.pages.dashboard';

    protected static string $routePath = '/dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    public function getTitle(): string
    {
        return 'لوحة التحكم';
    }

    public function getHeading(): string
    {
        return 'لوحة التحكم';
    }

    public function getSubheading(): ?string
    {
        return 'مركز التحكم الخاص بك';
    }

    public function getProfile()
    {
        return auth()->user()->profile;
    }

    public function getCompletionPercentage(): int
    {
        $profile = $this->getProfile();

        return $profile ? $profile->calculateCompletionPercentage() : 0;
    }

    public function getChecklist(): array
    {
        $profile = $this->getProfile();
        if (! $profile) {
            return [
                'profile_created' => false,
                'has_bio' => false,
                'portfolio_complete' => false,
                'credentials_added' => false,
                'contacts_added' => false,
            ];
        }

        $portfolioCount = $profile->portfolioItems()->count();

        return [
            'profile_created' => true,
            'has_bio' => ! empty($profile->bio),
            'portfolio_complete' => $portfolioCount >= 2,
            'projects_count' => $portfolioCount,
            'credentials_added' => $profile->credentials()->count() > 0,
            'contacts_added' => ! empty($profile->whatsapp) || ! empty(auth()->user()->phone),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }
}
