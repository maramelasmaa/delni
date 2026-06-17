<?php

namespace App\Filament\Support\Pages\Concerns;

use Filament\Actions\Action;

trait HasBackHeaderAction
{
    protected function getBackHeaderAction(): Action
    {
        return Action::make('back')
            ->label(__('filament.actions.back'))
            ->icon(app()->getLocale() === 'ar' ? 'heroicon-o-arrow-right' : 'heroicon-o-arrow-left')
            ->color('gray')
            ->url($this->getResource()::getUrl('index'));
    }
}
