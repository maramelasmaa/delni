<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\Category;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CategoryResource extends Resource
{
    use AdminAccessOnly;

    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.marketplace');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.category_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.category_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament.sections.translations'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.fields.name_en'))
                            ->placeholder(__('filament.placeholders.category_name_en'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_ar')
                            ->label(__('filament.fields.name_ar'))
                            ->placeholder(__('filament.placeholders.category_name_ar'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make(__('filament.sections.display'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->placeholder(__('filament.placeholders.slug_category'))
                            ->label(__('filament.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('icon');
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
                Tables\Columns\TextColumn::make('slug')->label('الرابط المختصر')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('icon.name')
                    ->label(__('filament.fields.icon'))
                    ->formatStateUsing(fn ($state, $record) => $state ? "🎨 {$state}" : '—')
                    ->description(fn ($record) => $record->icon ? route('icon.show', $record->icon) : 'لا توجد أيقونة')
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
                        $hasProfiles = Profile::whereIn('category_id', $records->pluck('id'))->exists();
                        if ($hasProfiles) {
                            Notification::make()
                                ->title(__('messages.cannot_delete_categories_active_profiles'))
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
            'index' => CategoryResource\Pages\ListCategories::route('/'),
            'create' => CategoryResource\Pages\CreateCategory::route('/create'),
            'edit' => CategoryResource\Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    private static function getIconPreview(?string $icon): string
    {
        if (empty($icon)) {
            return __('filament.help_text.icon_none');
        }

        // Check if it's an emoji
        if (mb_strlen($icon) <= 2 && preg_match('/\p{So}|\p{Sk}|\p{Sc}/u', $icon)) {
            return "Emoji: {$icon}";
        }

        // Check for Heroicon format
        if (preg_match('/^heroicon-(o|s)-(.+)$/', $icon, $matches)) {
            $style = $matches[1] === 'o' ? 'Outline' : 'Solid';
            $name = str_replace('-', ' ', $matches[2]);

            return "Heroicon ({$style}): {$name}";
        }

        // Check for Font Icon format
        if (strpos($icon, 'fi') === 0) {
            return "Font Icon: {$icon}";
        }

        return "Icon: {$icon}";
    }
}
