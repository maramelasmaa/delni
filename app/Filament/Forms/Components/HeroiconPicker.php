<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Services\IconSystem;
use Filament\Forms\Components\Select;

class HeroiconPicker extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('الأيقونة')
            ->placeholder('اختر أيقونة')
            ->options(IconSystem::getHeroiconsList())
            ->searchable()
            ->preload()
            ->optionsLimit(50)
            ->nullable();
    }
}
