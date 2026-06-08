<?php

namespace App\Filament\Forms\Components;

use App\Services\IconSystem;
use Filament\Forms\Components\Select;

class IconPicker extends Select
{
    protected string $view = 'filament.forms.components.icon-picker';

    public static function make(string $name): static
    {
        $component = parent::make($name);

        $component->options(IconSystem::getHeroiconsList())
            ->searchable()
            ->preload()
            ->live()
            ->nullable()
            ->columnSpanFull();

        return $component;
    }

    public function getStateUsing(mixed $state): mixed
    {
        return $state;
    }
}
