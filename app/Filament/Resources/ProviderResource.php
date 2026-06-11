<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\Profile;
use App\Models\ProviderType;
use App\Models\Subcategory;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\OnboardingLinkService;
use App\Services\SubscriptionValidationService;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProviderResource extends Resource
{
    use AdminAccessOnly;

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

                Action::make('resend_onboarding_link')
                    ->label(__('filament.actions.resend_onboarding_link'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (User $record, OnboardingLinkService $service) {
                        try {
                            $service->resend($record);
                            Notification::make()
                                ->title(__('filament.notifications.onboarding_link_sent'))
                                ->body(__('filament.notifications.onboarding_link_sent_body', ['email' => $record->email]))
                                ->success()
                                ->send();
                        } catch (\InvalidArgumentException $e) {
                            Notification::make()
                                ->title(__('filament.notifications.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } catch (Throwable $e) {
                            Log::error('Failed to resend provider onboarding email from table action', [
                                'provider_id' => $record->id,
                                'email' => $record->email,
                                'exception' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title(__('filament.notifications.error'))
                                ->body('Email could not be sent. Check the Laravel logs for the Resend error.')
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
        // Ensure relations are loaded to avoid N+1 queries
        $record?->loadMissing(['profile.stats', 'profile.subcategories', 'subscriptions']);

        $profile = $record?->profile;
        $stats = $profile?->stats;

        // Use pre-loaded subscriptions collection instead of query
        $subscription = $record?->subscriptions
            ->sortByDesc('starts_at')
            ->sortByDesc('id')
            ->first();

        // Get subcategory from pre-loaded collection instead of query
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
            'featured' => $stats?->is_featured ?? false,
            'featured_until' => $stats?->featured_until,
        ];

        return $data;
    }

    public static function accountData(array $data): array
    {
        return collect($data)
            ->only(['name', 'email', 'phone', 'password', 'is_active', 'is_suspended'])
            ->filter(fn ($value, string $key) => $key !== 'password' || filled($value))
            ->all();
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

            $profile->subcategories()->sync(filled($profileData['subcategory_id'] ?? null) ? [$profileData['subcategory_id']] : []);
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
            'is_featured' => $marketplaceData['featured'] ?? false,
            'featured_until' => $marketplaceData['featured_until'] ?? null,
        ]);

        static::saveProviderSubscription($record, $subscriptionData);
    }

    protected static function saveProviderSubscription(User $record, array $subscriptionData): void
    {
        if (! filled($subscriptionData['plan_id'] ?? null)) {
            return;
        }

        if (! filled($subscriptionData['id'] ?? null)) {
            app(SubscriptionValidationService::class)->createForProvider($record, [
                'plan_id' => $subscriptionData['plan_id'],
                'starts_at' => $subscriptionData['starts_at'],
                'ends_at' => $subscriptionData['ends_at'],
                'notes' => $subscriptionData['notes'] ?? null,
            ]);

            return;
        }

        $subscription = Subscription::query()->where('user_id', $record->id)->findOrFail($subscriptionData['id']);

        $subscription->fill([
            'user_id' => $record->id,
            'plan_id' => $subscriptionData['plan_id'],
            'starts_at' => $subscriptionData['starts_at'],
            'ends_at' => $subscriptionData['ends_at'],
            'is_active' => $subscriptionData['is_active'] ?? false,
            'approved_at' => $subscriptionData['approved_at'] ?? null,
            'processed_at' => $subscriptionData['processed_at'] ?? null,
            'notes' => $subscriptionData['notes'] ?? null,
        ]);

        if ($subscription->is_active || $subscription->approved_at !== null) {
            $subscription->approved_at ??= now();
            $subscription->approved_by ??= auth()->id();
            $subscription->processed_at ??= now();
            $subscription->processed_by ??= auth()->id();
        }

        $subscription->save();
    }

    protected static function profileSlug(User $user, array $data): string
    {
        $baseSlug = Str::slug($data['business_name'] ?? $user->name) ?: 'provider-'.$user->id;
        $slug = $baseSlug;
        $attempt = 1;

        while (Profile::query()
            ->where('slug', $slug)
            ->where('user_id', '!=', $user->id)
            ->exists()
        ) {
            $slug = $baseSlug.'-'.$user->id.'-'.$attempt;
            $attempt++;
        }

        return $slug;
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

    protected static function profileSummarySchema(): array
    {
        return [
            Section::make('ملف مقدم الخدمة')
                ->description('للقراءة فقط. مقدم الخدمة يدير هذه الحقول من لوحة مقدم الخدمة.')
                ->schema([
                    Forms\Components\Placeholder::make('profile_business_name')
                        ->label(__('filament.fields.business_name'))
                        ->content(fn (?User $record) => $record?->profile?->business_name ?? '-'),
                    Forms\Components\Placeholder::make('profile_provider_type')
                        ->label('نوع مقدم الخدمة')
                        ->content(fn (?User $record) => ProviderType::labelFor($record?->profile?->provider_type)),
                    Forms\Components\Placeholder::make('profile_remote')
                        ->label('يعمل عن بعد')
                        ->content(fn (?User $record) => $record?->profile?->offers_remote_work ? 'نعم' : 'لا'),
                    Forms\Components\Placeholder::make('profile_bio')
                        ->label(__('filament.fields.bio'))
                        ->content(fn (?User $record) => $record?->profile?->bio ?? '-')
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('profile_city')
                        ->label(__('filament.fields.city'))
                        ->content(fn (?User $record) => $record?->profile?->city?->localized_name ?? '-'),
                    Forms\Components\Placeholder::make('profile_category')
                        ->label(__('filament.fields.category'))
                        ->content(fn (?User $record) => $record?->profile?->category?->localized_name ?? '-'),
                    Forms\Components\Placeholder::make('profile_subcategories')
                        ->label(__('filament.fields.subcategories'))
                        ->content(fn (?User $record) => $record?->profile?->subcategories?->pluck('localized_name')->join(', ') ?: '-'),
                    Forms\Components\Placeholder::make('profile_service_area_note')
                        ->label('ملاحظات نطاق الخدمة')
                        ->content(fn (?User $record) => $record?->profile?->service_area_note ?? '-')
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('profile_map_url')
                        ->label('رابط الخريطة')
                        ->content(fn (?User $record) => $record?->profile?->map_url ?? '-')
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('profile_whatsapp')
                        ->label(__('filament.fields.whatsapp'))
                        ->content(fn (?User $record) => $record?->profile?->whatsapp ?? '-'),
                    Forms\Components\Placeholder::make('profile_phone')
                        ->label('هاتف الملف')
                        ->content(fn (?User $record) => $record?->profile?->phone ?? '-'),
                    Forms\Components\Placeholder::make('profile_complete')
                        ->label(__('filament.fields.complete'))
                        ->content(fn (?User $record) => $record?->profile?->is_complete ? 'نعم' : 'لا'),
                ])
                ->columns(2),
        ];
    }

    protected static function subscriptionSchema(): array
    {
        return [
            Section::make('الاشتراك الحالي / الأخير')
                ->schema([
                    Forms\Components\Hidden::make('subscription.id'),
                    Forms\Components\Select::make('subscription.plan_id')
                        ->label(__('filament.fields.plan'))
                        ->options(fn () => SubscriptionPlan::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->live(),
                    Forms\Components\DatePicker::make('subscription.starts_at')
                        ->label(__('filament.fields.started_at'))
                        ->required(fn ($get) => filled($get('subscription.plan_id'))),
                    Forms\Components\DatePicker::make('subscription.ends_at')
                        ->label(__('filament.fields.ends_at'))
                        ->required(fn ($get) => filled($get('subscription.plan_id'))),
                    Forms\Components\Toggle::make('subscription.is_active')
                        ->label(__('filament.fields.active'))
                        ->default(true),
                    Forms\Components\Textarea::make('subscription.notes')
                        ->label('ملاحظات')
                        ->placeholder('أي ملاحظات إضافية')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    protected static function marketplaceSchema(): array
    {
        return [
            Section::make('مواضع الظهور في السوق')
                ->schema([
                    Forms\Components\Toggle::make('marketplace.homepage_featured')->label('مميز في الصفحة الرئيسية')->live(),
                    Forms\Components\DatePicker::make('marketplace.homepage_featured_until')
                        ->label('مميز في الرئيسية حتى')
                        ->required(fn ($get) => $get('marketplace.homepage_featured')),
                    Forms\Components\Toggle::make('marketplace.top_search')->label('أعلى البحث')->live(),
                    Forms\Components\DatePicker::make('marketplace.top_search_until')
                        ->label('أعلى البحث حتى')
                        ->required(fn ($get) => $get('marketplace.top_search')),
                    Forms\Components\Toggle::make('marketplace.top_category')->label('أعلى التصنيف')->live(),
                    Forms\Components\DatePicker::make('marketplace.top_category_until')
                        ->label('أعلى التصنيف حتى')
                        ->required(fn ($get) => $get('marketplace.top_category')),
                    Forms\Components\Toggle::make('marketplace.top_subcategory')->label('أعلى الفئة الفرعية')->live(),
                    Forms\Components\DatePicker::make('marketplace.top_subcategory_until')
                        ->label('أعلى الفئة الفرعية حتى')
                        ->required(fn ($get) => $get('marketplace.top_subcategory')),
                    Forms\Components\Toggle::make('marketplace.featured')->label(__('filament.fields.featured'))->live(),
                    Forms\Components\DatePicker::make('marketplace.featured_until')
                        ->label(__('filament.fields.featured_until'))
                        ->required(fn ($get) => $get('marketplace.featured')),
                ])
                ->columns(2),
        ];
    }

    protected static function validateProviderData(User $record, array $data): void
    {
        $profile = $data['profile'] ?? [];
        $subscription = $data['subscription'] ?? [];
        $marketplace = $data['marketplace'] ?? [];

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

        if (filled($subscription['plan_id'] ?? null)) {
            if (! filled($subscription['starts_at'] ?? null) || ! filled($subscription['ends_at'] ?? null)) {
                throw ValidationException::withMessages([
                    'subscription.starts_at' => 'تاريخ بداية الاشتراك وتاريخ انتهائه مطلوبان.',
                ]);
            }

            $startsAt = Carbon::parse($subscription['starts_at'])->startOfDay();
            $endsAt = Carbon::parse($subscription['ends_at'])->startOfDay();

            if ($endsAt->lessThanOrEqualTo($startsAt)) {
                throw ValidationException::withMessages([
                    'subscription.ends_at' => 'يجب أن يكون تاريخ انتهاء الاشتراك بعد تاريخ البداية.',
                ]);
            }

            $overlaps = Subscription::query()
                ->where('user_id', $record->id)
                ->when($subscription['id'] ?? null, fn (Builder $query, $id) => $query->whereKeyNot($id))
                ->whereDate('starts_at', '<=', $endsAt)
                ->whereDate('ends_at', '>=', $startsAt)
                ->exists();

            if ($overlaps) {
                throw ValidationException::withMessages([
                    'subscription.starts_at' => 'هذا الاشتراك يتداخل مع اشتراك آخر لنفس مقدم الخدمة.',
                ]);
            }
        }

        foreach ([
            'homepage_featured' => 'homepage_featured_until',
            'top_search' => 'top_search_until',
            'top_category' => 'top_category_until',
            'top_subcategory' => 'top_subcategory_until',
            'featured' => 'featured_until',
        ] as $enabledField => $untilField) {
            if (($marketplace[$enabledField] ?? false) && ! filled($marketplace[$untilField] ?? null)) {
                throw ValidationException::withMessages([
                    "marketplace.{$untilField}" => 'تاريخ الانتهاء مطلوب عند تفعيل هذا الموضع.',
                ]);
            }

            if (($marketplace[$enabledField] ?? false) && Carbon::parse($marketplace[$untilField])->endOfDay()->isPast()) {
                throw ValidationException::withMessages([
                    "marketplace.{$untilField}" => 'يجب أن يكون تاريخ انتهاء الموضع في المستقبل.',
                ]);
            }
        }
    }

    protected static function latestSubscriptionIsApproved(?User $record): bool
    {
        return (bool) $record?->subscriptions()
            ->latest('starts_at')
            ->latest('id')
            ->first()?->approved_at;
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
