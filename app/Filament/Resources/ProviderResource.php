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
                        Tabs\Tab::make('الاشتراك')->schema(static::subscriptionSchema()),
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
                Tables\Columns\TextColumn::make('email')->label('البريد الإلكتروني')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label(__('filament.fields.phone')),
                Tables\Columns\IconColumn::make('is_active')->label(__('filament.fields.active'))->boolean()->sortable(),
                Tables\Columns\IconColumn::make('is_suspended')->label(__('filament.fields.suspended'))->boolean()->sortable(),
                Tables\Columns\IconColumn::make('security_flagged')->label(__('filament.fields.security_flagged'))->boolean()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('filament.fields.created_at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label(__('filament.filters.active')),
                Tables\Filters\Filter::make('suspended')
                    ->query(fn ($query) => $query->where('is_suspended', true))
                    ->label(__('filament.filters.suspended')),
            ])
            ->paginated([25, 50, 100])
            ->recordActions([
                EditAction::make()
                    ->modal(),

                Action::make('generate_onboarding_link')
                    ->label('Generate setup link')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (User $record, OnboardingLinkService $service) {
                        try {
                            $setupLink = $service->createOrRefreshLink($record);
                            Notification::make()
                                ->title('Provider setup link ready')
                                ->body("Copy this link and send it manually to {$record->email}: {$setupLink}")
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
                                ->body('Setup link could not be generated. Check the Laravel logs.')
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
            ->with(['profile.stats', 'subscriptions']);
    }

    public static function fillProviderFormData(array $data, ?User $record): array
    {
        $record?->loadMissing(['profile.stats', 'profile.subcategories', 'subscriptions']);

        $profile = $record?->profile;
        $stats = $profile?->stats;

        $subscription = $record?->subscriptions
            ->sortByDesc('starts_at')
            ->sortByDesc('id')
            ->first();

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
        ];
        $data['subscription'] = [
            'id' => $subscription?->id,
            'plan_id' => $subscription?->plan_id,
            'starts_at' => $subscription?->starts_at,
            'ends_at' => $subscription?->ends_at,
            'is_active' => $subscription?->is_active ?? false,
            'approved_at' => $subscription?->approved_at,
            'processed_at' => $subscription?->processed_at,
            'notes' => $subscription?->notes,
        ];
        $data['marketplace'] = [
            'homepage_featured' => $stats?->is_homepage_featured ?? false,
            'homepage_featured_until' => $stats?->homepage_featured_until,
            'top_search' => $stats?->is_top_search ?? false,
            'top_search_until' => $stats?->top_search_until,
            'top_category' => $stats?->is_top_category ?? false,
            'top_category_until' => $stats?->top_category_until,
            'top_subcategory' => $stats?->is_top_subcategory ?? false,
            'top_subcategory_until' => $stats?->top_subcategory_until,
        ];

        return $data;
    }

    public static function saveProviderData(User $record, array $data): void
    {
        static::validateProviderData($record, $data);

        $profileData = $data['profile'] ?? [];
        $subscriptionData = $data['subscription'] ?? [];
        $marketplaceData = $data['marketplace'] ?? [];

        $hasEditableProfileData = collect($profileData)->contains(fn ($value) => filled($value));
        $hasMarketplaceData = collect($marketplaceData)->contains(fn ($value) => filled($value) && $value !== false);

        if (! $hasEditableProfileData && ! $hasMarketplaceData) {
            static::saveProviderSubscription($record, $subscriptionData);

            return;
        }

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
            static::saveProviderSubscription($record, $subscriptionData);

            return;
        }

        $stats = $profile->stats()->firstOrCreate(['profile_id' => $profile->id]);
        $stats->update([
            'is_homepage_featured' => $marketplaceData['homepage_featured'] ?? false,
            'homepage_featured_until' => $marketplaceData['homepage_featured_until'] ?? null,
            'is_top_search' => $marketplaceData['top_search'] ?? false,
            'top_search_until' => $marketplaceData['top_search_until'] ?? null,
            'is_top_category' => $marketplaceData['top_category'] ?? false,
            'top_category_until' => $marketplaceData['top_category_until'] ?? null,
            'is_top_subcategory' => $marketplaceData['top_subcategory'] ?? false,
            'top_subcategory_until' => $marketplaceData['top_subcategory_until'] ?? null,
        ]);

        static::saveProviderSubscription($record, $subscriptionData);
    }

    protected static function accountSchema(): array
    {
        return [
            Section::make(__('filament.sections.account_details'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament.fields.name'))
                        ->placeholder('مثال: أحمد حسن')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->placeholder('provider@example.com')
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email', ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('filament.fields.phone'))
                        ->placeholder('+218910000000')
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
                    'profile.subcategory_id' => 'يجب أن تكون الفئة الفرعية تابعة للتصنيف المحدد.',
                ]);
            }
        }

        static::validateSubscriptionData($record, $data['subscription'] ?? []);
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
