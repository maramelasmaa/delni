<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Filament\Resources\Traits\HasProviderFormSupport;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\OnboardingLinkService;
use App\Services\UserSuspensionService;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProviderResource extends Resource
{
    use AdminAccessOnly;
    use HasProviderFormSupport;

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.providers');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.provider_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.provider');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.provider_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('إدارة مقدم الخدمة')
                    ->tabs([
                        Tabs\Tab::make('الحساب')->schema(static::accountSchema()),
                        Tabs\Tab::make('الملف')->schema(static::profileSummarySchema()),
                        Tabs\Tab::make('صلاحية الظهور')->schema(static::accessSchema()),
                        Tabs\Tab::make('السوق')->schema(static::marketplaceSchema()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('filament.fields.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label(__('filament.fields.email'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label(__('filament.fields.phone')),
                Tables\Columns\IconColumn::make('is_active')->label(__('filament.fields.active'))->boolean()->sortable(),
                Tables\Columns\IconColumn::make('is_suspended')->label(__('filament.fields.suspended'))->boolean()->sortable(),
                Tables\Columns\IconColumn::make('security_flagged')->label(__('filament.fields.security_flagged'))->boolean()->sortable(),
                Tables\Columns\TextColumn::make('profile.provider_access_ends_at')
                    ->label(__('filament.widgets.subscription_ends'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('filament.widgets.unavailable')),
                Tables\Columns\TextColumn::make('created_at')->label(__('filament.fields.created_at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label(__('filament.filters.active')),
                Tables\Filters\Filter::make('suspended')
                    ->query(fn ($query) => $query->where('is_suspended', true))
                    ->label(__('filament.filters.suspended')),
                Tables\Filters\Filter::make('has_access')
                    ->query(fn ($query) => $query->whereHas('profile', fn ($q) => $q->whereNotNull('provider_access_ends_at')->where('provider_access_ends_at', '>=', now())))
                    ->label('لديه صلاحية ظهور نشطة'),
            ])
            ->paginated([25, 50, 100])
            ->recordActions([
                EditAction::make()
                    ->modal(),

                Action::make('extend_access_30')
                    ->label('تمديد 30 يوم')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => static::extendProviderAccess($record, 30)),

                Action::make('extend_access_90')
                    ->label('تمديد 90 يوم')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => static::extendProviderAccess($record, 90)),

                Action::make('extend_access_365')
                    ->label('تمديد سنة')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => static::extendProviderAccess($record, 365)),

                Action::make('generate_onboarding_link')
                    ->label(__('filament.actions.generate_setup_link'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (User $record, OnboardingLinkService $service) {
                        try {
                            $setupLink = $service->createOrRefreshLink($record);
                            Notification::make()
                                ->title(__('filament.notifications.provider_setup_link_ready'))
                                ->body(__('filament.notifications.setup_link_copy_send', ['email' => $record->email, 'link' => $setupLink]))
                                ->success()
                                ->persistent()
                                ->send();
                        } catch (\InvalidArgumentException $e) {
                            Notification::make()
                                ->title(__('filament.notifications.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } catch (Throwable $e) {
                            Log::error('Failed to generate provider onboarding link from table action', [
                                'provider_id' => $record->id,
                                'email' => $record->email,
                                'exception' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title(__('filament.notifications.error'))
                                ->body(__('filament.help_text.setup_link_logs'))
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('suspend')
                    ->label(__('filament.actions.suspend'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! $record->is_suspended && $record->id !== auth()->id())
                    ->schema([
                        Forms\Components\Textarea::make('suspension_reason')
                            ->label(__('filament.fields.reason'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data, UserSuspensionService $service): void {
                        $service->suspend($record, $data['suspension_reason']);
                    }),

                Action::make('reinstate')
                    ->label(__('filament.actions.reinstate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->is_suspended)
                    ->schema([
                        Forms\Components\Textarea::make('reinstatement_reason')
                            ->label(__('filament.fields.reason'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data, UserSuspensionService $service): void {
                        $service->reinstate($record, $data['reinstatement_reason']);
                    }),

                Action::make('clear_security_flag')
                    ->label(__('filament.actions.clear_security_flag'))
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->security_flagged)
                    ->action(function ($record): void {
                        $record->update(['security_flagged' => false]);
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'provider'))
            ->with(['profile.stats']);
    }

    public static function fillProviderFormData(array $data, ?User $record): array
    {
        $record?->loadMissing(['profile.stats', 'profile.subcategories']);

        $profile = $record?->profile;
        $stats = $profile?->stats;

        $subcategoryId = $profile?->subcategories?->first()?->id;

        $data['profile'] = [
            'business_name' => $profile?->business_name,
            'provider_type' => $profile?->provider_type,
            'offers_remote_work' => $profile?->offers_remote_work,
            'bio' => $profile?->bio,
            'city_id' => $profile?->city_id,
            'category_id' => $profile?->category_id,
            'subcategory_id' => $subcategoryId,
            'service_area_note' => $profile?->service_area_note,
            'map_url' => $profile?->map_url,
            'whatsapp' => $profile?->whatsapp,
            'phone' => $profile?->phone,
            'provider_access_ends_at' => $profile?->provider_access_ends_at,
        ];
        $data['marketplace'] = [
            'homepage_featured' => $stats?->is_homepage_featured ?? false,
            'homepage_featured_until' => $stats?->homepage_featured_until,
        ];

        return $data;
    }

    public static function saveProviderData(User $record, array $data): void
    {
        static::validateProviderData($record, $data);

        $profileData = $data['profile'] ?? [];
        $marketplaceData = $data['marketplace'] ?? [];

        $hasEditableProfileData = collect($profileData)
            ->except('provider_access_ends_at')
            ->contains(fn ($value) => filled($value));
        $hasMarketplaceData = array_key_exists('homepage_featured', $marketplaceData)
            || array_key_exists('homepage_featured_until', $marketplaceData);

        $profile = $record->profile;

        if ($hasEditableProfileData) {
            $profile = $record->profile()->updateOrCreate(
                ['user_id' => $record->id],
                [
                    'business_name' => $profileData['business_name'] ?? null,
                    'type' => 'business',
                    'bio' => $profileData['bio'] ?? null,
                    'city_id' => $profileData['city_id'] ?? null,
                    'category_id' => $profileData['category_id'] ?? null,
                    'whatsapp' => $profileData['whatsapp'] ?? null,
                    'phone' => $profileData['phone'] ?? null,
                    'slug' => static::profileSlug($record, $profileData),
                ],
            );

            $profile->subcategories()->sync(
                filled($profileData['subcategory_id'] ?? null) ? [$profileData['subcategory_id']] : []
            );
        }

        if (! $profile) {
            return;
        }

        static::saveProviderAccess($profile, $profileData['provider_access_ends_at'] ?? null);

        if ($hasMarketplaceData) {
            static::saveHomepagePlacement($profile, $marketplaceData);
        }
    }

    protected static function accountSchema(): array
    {
        return [
            Section::make(__('filament.sections.account_details'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament.fields.name'))
                        ->placeholder(__('filament.placeholders.provider_name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label(__('filament.fields.email'))
                        ->email()
                        ->placeholder('provider@example.com')
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email', ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('filament.fields.phone'))
                        ->placeholder(__('filament.placeholders.phone'))
                        ->maxLength(20),
                ])
                ->columns(2),
            Section::make(__('filament.sections.account_status'))
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('filament.fields.active'))
                        ->default(true),
                    Forms\Components\Toggle::make('is_suspended')
                        ->label(__('filament.fields.suspended'))
                        ->default(false),
                ])
                ->columns(2),
        ];
    }

    protected static function validateProviderData(User $record, array $data): void
    {
        $profile = $data['profile'] ?? [];

        if (filled($profile['subcategory_id'] ?? null)) {
            $validSubcategory = Subcategory::query()
                ->whereKey($profile['subcategory_id'])
                ->where('category_id', $profile['category_id'] ?? null)
                ->exists();

            if (! $validSubcategory) {
                throw ValidationException::withMessages([
                    'profile.subcategory_id' => 'يجب أن يكون التخصص الفرعي تابعاً للتصنيف المحدد.',
                ]);
            }
        }

        static::validateMarketplaceData($data['marketplace'] ?? []);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ProviderResource\Pages\ListProviders::route('/'),
            'create' => ProviderResource\Pages\CreateProvider::route('/create'),
            'edit' => ProviderResource\Pages\EditProvider::route('/{record}/edit'),
        ];
    }
}
