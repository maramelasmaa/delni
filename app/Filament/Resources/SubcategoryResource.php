<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\Subcategory;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubcategoryResource extends Resource
{
    use AdminAccessOnly;

    protected static ?string $model = Subcategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 31;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.marketplace');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.subcategory_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.subcategory');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.subcategory_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament.sections.category_section'))
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label(__('filament.fields.category'))
                            ->placeholder(__('filament.placeholders.select_category'))
                            ->relationship('category', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->localized_name)
                            ->required(),
                    ]),

                Section::make(__('filament.sections.translations'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.fields.name_en'))
                            ->placeholder(__('filament.placeholders.subcategory_name_en'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_ar')
                            ->label(__('filament.fields.name_ar'))
                            ->placeholder(__('filament.placeholders.subcategory_name_ar'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make(__('filament.sections.display'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label(__('filament.fields.slug'))
                            ->placeholder('emergency-repairs')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->hint(__('filament.help_text.slug')),
                        Forms\Components\FileUpload::make('svg_file')
                            ->label(__('filament.fields.icon'))
                            ->acceptedFileTypes(['image/svg+xml'])
                            ->maxSize(500)
                            ->storeFiles(false)
                            ->nullable()
                            ->hint(__('filament.help_text.svg_upload')),
                        Forms\Components\TextInput::make('sort_order')
                            ->placeholder(__('filament.placeholders.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->hint(__('filament.help_text.sort_order')),
                    ])
                    ->columns(3),

                Section::make(__('filament.sections.status_section'))
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
                Tables\Columns\TextColumn::make('category.localized_name')
                    ->label(__('filament.fields.category'))
                    ->state(fn ($record) => $record->category->localized_name)
                    ->sortable('category.name')
                    ->searchable('category.name'),
                Tables\Columns\TextColumn::make('localized_name')
                    ->state(fn ($record) => $record->localized_name)
                    ->label(__('filament.fields.name'))
                    ->searchable('name')
                    ->sortable('name'),
                Tables\Columns\TextColumn::make('slug')->label(__('filament.fields.slug'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('icon.name')
                    ->label(__('filament.fields.icon'))
                    ->formatStateUsing(fn ($state, $record) => $state ? "🎨 {$state}" : '—')
                    ->description(fn ($record) => $record->icon ? route('icon.show', $record->icon) : __('filament.help_text.icon_none'))
                    ->searchable('icon.name')
                    ->sortable('icon.name'),
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
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->localized_name),
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
                DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('category', 'icon');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => SubcategoryResource\Pages\ListSubcategories::route('/'),
            'create' => SubcategoryResource\Pages\CreateSubcategory::route('/create'),
            'edit' => SubcategoryResource\Pages\EditSubcategory::route('/{record}/edit'),
        ];
    }
}
