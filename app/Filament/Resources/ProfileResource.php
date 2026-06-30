<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\Profile;
use App\Models\ProviderType;
use App\Services\ProfileImageService;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class ProfileResource extends Resource
{
    use AdminAccessOnly;

    protected static ?string $model = Profile::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.providers');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.profile_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.profile');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.profile_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('الأساسيات')
                    ->description('معلومات مقدم الخدمة الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('business_name')
                            ->label(__('filament.fields.business_name'))
                            ->required(),
                        Forms\Components\Select::make('provider_type')
                            ->label(__('filament.fields.provider_type'))
                            ->options(fn () => ProviderType::options(activeOnly: true))
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label(__('filament.fields.category'))
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('city_id')
                            ->label(__('filament.fields.city'))
                            ->relationship('city', 'name')
                            ->searchable()
                            ->required(),
                        Forms\Components\Textarea::make('bio')
                            ->label(__('filament.fields.bio'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('filament.sections.reputation'))
                    ->description('مؤشرات السمعة للقراءة فقط')
                    ->schema([
                        Forms\Components\Placeholder::make('stats.rating_avg')
                            ->label(__('filament.fields.rating_avg_short'))
                            ->content(fn ($record) => $record?->stats?->rating_avg ?? '0.0'),
                        Forms\Components\Placeholder::make('stats.reviews_count')
                            ->label(__('filament.fields.reviews_count'))
                            ->content(fn ($record) => $record?->stats?->reviews_count ?? '0'),
                        Forms\Components\Placeholder::make('stats.is_top_rated')
                            ->label('الأعلى تقييماً')
                            ->content(fn ($record) => $record?->stats?->is_top_rated ? 'نعم (محسوبة تلقائياً)' : 'لا'),
                    ])
                    ->columns(3),

                Section::make('قابلية الظهور')
                    ->description('حالة النظام')
                    ->schema([
                        Forms\Components\Placeholder::make('is_complete')
                            ->label('الملف مكتمل')
                            ->content(fn ($record) => $record?->is_complete ? 'نعم' : 'لا'),
                    ]),

                Section::make(__('filament.sections.images'))
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label(__('filament.fields.avatar'))
                            ->image()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->saveUploadedFileUsing(function (UploadedFile $file, Profile $record, ProfileImageService $imageService) {
                                return $imageService->replaceImage($record->logo, $file, 'avatar');
                            })
                            ->deleteUploadedFileUsing(function ($file, ProfileImageService $imageService) {
                                $imageService->deleteImage($file);
                            }),
                        Forms\Components\FileUpload::make('cover_image')
                            ->label(__('filament.fields.cover_image'))
                            ->image()
                            ->maxSize(4096)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->saveUploadedFileUsing(function (UploadedFile $file, Profile $record, ProfileImageService $imageService) {
                                return $imageService->replaceImage($record->cover_image, $file, 'cover');
                            })
                            ->deleteUploadedFileUsing(function ($file, ProfileImageService $imageService) {
                                $imageService->deleteImage($file);
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('business_name')
                    ->label(__('filament.fields.business_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.fields.provider_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('provider_type.localized_name')
                    ->label(__('filament.fields.provider_type'))
                    ->sortable(),
                // Column names use the REAL relationship path so Filament auto-joins for sort.
                // Were make('category.localized_name')->sortable('category.name'): the string
                // coerced to `true` and sorted by the localized_name accessor → SQL error.
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('filament.fields.category'))
                    ->state(fn ($record) => $record->category?->localized_name ?? '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label(__('filament.fields.city'))
                    ->state(fn ($record) => $record->city?->localized_name ?? '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stats.rating_avg')
                    ->label(__('filament.fields.rating_avg_short'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_complete')
                    ->label(__('filament.fields.complete'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query) => $query->whereHas('user', fn ($q) => $q->where('is_active', true)))
                    ->label(__('filament.filters.active_user')),
                Tables\Filters\Filter::make('complete')
                    ->query(fn (Builder $query) => $query->where('is_complete', true))
                    ->label(__('filament.filters.complete')),
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
        return parent::getEloquentQuery()
            ->with(['user', 'category', 'city', 'stats']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ProfileResource\Pages\ListProfiles::route('/'),
            'edit' => ProfileResource\Pages\EditProfile::route('/{record}/edit'),
        ];
    }
}
