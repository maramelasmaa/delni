<?php

namespace App\Filament\Resources\ContactInfos\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactInfosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('whatsapp')
                    ->label(__('filament.fields.whatsapp'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('filament.fields.phone'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('filament.fields.email'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
