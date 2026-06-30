<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\Profile;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketplacePlacementResource extends Resource
{
    use AdminAccessOnly;

    protected static ?string $model = Profile::class;

    protected static ?string $slug = 'marketplace-placements';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.marketplace');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.marketplace_placement_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.marketplace_placement');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.marketplace_placement_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('مواضع الظهور')
                    ->tabs([
                        Tabs\Tab::make('الرئيسية')
                            ->schema([
                                Section::make('الظهور المميز في الرئيسية')
                                    ->schema([
                                        Forms\Components\Toggle::make('stats.is_homepage_featured')
                                            ->label('مميز في الصفحة الرئيسية'),
                                        Forms\Components\DatePicker::make('stats.homepage_featured_until')
                                            ->label('ينتهي في')
                                            ->visible(fn ($get) => $get('stats.is_homepage_featured')),
                                    ]),
                            ]),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.fields.provider'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('filament.fields.category'))
                    ->state(fn ($record) => $record->category?->localized_name ?? '—')
                    ->sortable(),
                Tables\Columns\IconColumn::make('stats.is_homepage_featured')
                    ->label('الرئيسية')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label(__('filament.fields.created_at'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('homepage_featured')
                    ->query(fn (Builder $query) => $query->whereHas('stats', fn ($q) => $q->where('is_homepage_featured', true)))
                    ->label('مميز في الرئيسية'),
            ])
            ->paginated([25, 50, 100])
            ->recordActions([
                EditAction::make()
                    ->modal(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('stats');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => MarketplacePlacementResource\Pages\ListMarketplacePlacements::route('/'),
            'edit' => MarketplacePlacementResource\Pages\EditMarketplacePlacement::route('/{record}/edit'),
        ];
    }
}
