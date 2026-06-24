<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->width(120)
                    ->height(60),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('link_type')
                    ->label('الرابط')
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('ينتهي في')
                    ->dateTime('Y-m-d')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('active')
                    ->label('المفعّلة فقط')
                    ->query(fn ($query) => $query->where('is_active', true)),
            ])
            ->defaultSort('sort_order')
            ->paginated([25, 50])
            ->recordActions([
                EditAction::make()->modal(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
