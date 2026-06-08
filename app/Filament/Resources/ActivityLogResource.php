<?php

namespace App\Filament\Resources;

use App\Models\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 51;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.system');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.activity_log');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.activity_log_plural');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.activity_log_plural');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament.sections.log_details'))
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label(__('filament.fields.id')),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label(__('filament.fields.user')),
                        Infolists\Components\TextEntry::make('action')
                            ->label(__('filament.fields.action')),
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('filament.fields.description'))
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('subject_type')
                            ->label(__('filament.fields.subject_type')),
                        Infolists\Components\TextEntry::make('subject_id')
                            ->label(__('filament.fields.subject_id')),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('filament.fields.logged_at'))
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make(__('filament.sections.properties'))
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('properties')
                            ->label(__('filament.sections.properties'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.fields.user'))
                    ->searchable()
                    ->sortable()
                    ->default(__('filament.fields.system')),
                Tables\Columns\TextColumn::make('action')
                    ->label(__('filament.fields.action'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('filament.fields.description'))
                    ->limit(60),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('filament.fields.subject'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_user')
                    ->query(fn (Builder $query) => $query->whereNotNull('user_id'))
                    ->label(__('filament.filters.has_user')),
            ])
            ->paginated([25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function canView(mixed $record): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ActivityLogResource\Pages\ListActivityLogs::route('/'),
            'view' => ActivityLogResource\Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
