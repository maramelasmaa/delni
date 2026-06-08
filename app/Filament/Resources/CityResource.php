<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\HeroiconPicker;
use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\City;
use App\Models\Profile;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CityResource extends Resource
{
    use AdminAccessOnly;

    protected static ?string $model = City::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 32;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.marketplace');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.city_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.city');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.city_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('الترجمات')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.fields.name_en'))
                            ->placeholder('Tripoli')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_ar')
                            ->label(__('filament.fields.name_ar'))
                            ->placeholder('مثال: طرابلس')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('العرض')
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('الرابط المختصر')
                            ->placeholder('tripoli')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->hint('معرف مناسب للرابط'),
                        HeroiconPicker::make('icon')
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('الحالة')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('localized_name')
                    ->state(fn ($record) => $record->localized_name)
                    ->label(__('filament.fields.name'))
                    ->searchable('name')
                    ->sortable('name'),
                Tables\Columns\TextColumn::make('icon')
                    ->label('الأيقونة')
                    ->formatStateUsing(fn ($state) => $state ?? '—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.fields.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label(__('filament.filters.active')),
                Tables\Filters\Filter::make('inactive')
                    ->query(fn ($query) => $query->where('is_active', false))
                    ->label(__('filament.filters.inactive')),
            ])
            ->paginated([25, 50, 100])
            ->recordActions([
                EditAction::make()
                    ->modal(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->before(function (DeleteBulkAction $action, Collection $records): void {
                        $hasProfiles = Profile::whereIn('city_id', $records->pluck('id'))->exists();
                        if ($hasProfiles) {
                            Notification::make()
                                ->title(__('messages.cannot_delete_cities_active_profiles'))
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => CityResource\Pages\ListCities::route('/'),
            'create' => CityResource\Pages\CreateCity::route('/create'),
            'edit' => CityResource\Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
