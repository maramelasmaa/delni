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

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }
}
