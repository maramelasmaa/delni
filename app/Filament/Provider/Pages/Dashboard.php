<?php

declare(strict_types=1);

namespace App\Filament\Provider\Pages;

use App\Filament\Provider\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

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
        return null;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }
}
