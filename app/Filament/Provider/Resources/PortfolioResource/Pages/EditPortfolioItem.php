<?php

namespace App\Filament\Provider\Resources\PortfolioResource\Pages;

use App\Filament\Provider\Resources\PortfolioResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use Filament\Actions;

class EditPortfolioItem extends EditRecordWithBack
{
    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
