<?php

declare(strict_types=1);

namespace App\Filament\Provider\Pages;

use App\Filament\Provider\Widgets\StatsOverviewWidget;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string $routePath = '/dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.provider.pages.dashboard';

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
        return null;
    }

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }
}
