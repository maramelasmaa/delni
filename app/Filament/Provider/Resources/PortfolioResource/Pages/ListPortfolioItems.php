<?php

namespace App\Filament\Provider\Resources\PortfolioResource\Pages;

use App\Filament\Provider\Resources\PortfolioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioItems extends ListRecords
{
    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $profile = $user?->profile;
        $portfolioCount = $profile?->portfolioItems()->count() ?? 0;
        $maxPortfolios = 2;
        $canAdd = $portfolioCount < $maxPortfolios;

        return [
            Actions\CreateAction::make()
                ->label('إضافة مشروع')
                ->disabled(! $canAdd)
                ->tooltip(
                    ! $canAdd
                        ? 'لقد وصلت إلى الحد الأقصى من المشاريع ('.$maxPortfolios.')'
                        : 'إضافة مشروع جديد'
                ),
        ];
    }
}
