<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\HeroiconPicker;
use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\Profile;
use App\Models\ProviderType;
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

class ProviderTypeResource extends Resource
{
    use AdminAccessOnly;

    protected static ?string $model = ProviderType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 33;

    public static function getNavigationLabel(): string
    {
        return __('filament.models.provider_type_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.provider_type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.provider_type_plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.marketplace');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('بيانات النوع')
                    ->schema([
                        Forms\Components\TextInput::make('name_ar')
                            ->label(__('filament.fields.name_ar'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.fields.name_en'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('الكود')
                            ->helperText('للاستخدام الداخلي فقط. استخدم أحرفاً صغيرة وواصلات.')
                            ->required()
                            ->maxLength(80)
                            ->regex('/^[a-z0-9-]+$/')
                            ->disabledOn('edit')
                            ->unique(ignoreRecord: true),
                        HeroiconPicker::make('icon')
                            ->nullable(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('filament.fields.sort_order'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('filament.fields.active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('localized_name')
                    ->state(fn (ProviderType $record): string => $record->localized_name)
                    ->label(__('filament.fields.name'))
                    ->searchable(['name', 'name_ar'])
                    // localized_name is an accessor; sort must pass an ARRAY of real columns
                    // (sortable('name') coerced to `true` → sorted the accessor → SQL error).
                    ->sortable(['name']),
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('icon')
                    ->label('الأيقونة')
                    ->formatStateUsing(fn ($state) => $state ?? '—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('filament.fields.sort_order'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.fields.active'))
                    ->boolean()
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
                EditAction::make()->modal(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->before(function (DeleteBulkAction $action, Collection $records): void {
                        $usedCodes = Profile::query()
                            ->whereIn('provider_type', $records->pluck('code'))
                            ->exists();

                        if ($usedCodes) {
                            Notification::make()
                                ->title('لا يمكن حذف نوع مستخدم في ملفات مقدمي الخدمة.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ProviderTypeResource\Pages\ListProviderTypes::route('/'),
            'create' => ProviderTypeResource\Pages\CreateProviderType::route('/create'),
            'edit' => ProviderTypeResource\Pages\EditProviderType::route('/{record}/edit'),
        ];
    }
}
