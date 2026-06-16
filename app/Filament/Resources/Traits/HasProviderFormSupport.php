<?php

namespace App\Filament\Resources\Traits;

use App\Models\Profile;
use App\Models\ProviderType;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionValidationService;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait HasProviderFormSupport
{
    protected static function subscriptionSchema(): array
    {
        return [
            Section::make('الاشتراك الحالي / الأخير')
                ->schema([
                    Forms\Components\Hidden::make('subscription.id'),
                    Forms\Components\Select::make('subscription.plan_id')
                        ->label(__('filament.fields.plan'))
                        ->options(fn () => SubscriptionPlan::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id'))
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
                    Forms\Components\Toggle::make('marketplace.homepage_featured')
                        ->label('مميز في الصفحة الرئيسية')
                        ->live(),
                    Forms\Components\DatePicker::make('marketplace.homepage_featured_until')
                        ->label('مميز في الرئيسية حتى')
                        ->visible(fn ($get) => $get('marketplace.homepage_featured'))
                        ->required(fn ($get) => $get('marketplace.homepage_featured')),
                    Forms\Components\Toggle::make('marketplace.top_search')
                        ->label('أعلى البحث')
                        ->live(),
                    Forms\Components\DatePicker::make('marketplace.top_search_until')
                        ->label('أعلى البحث حتى')
                        ->visible(fn ($get) => $get('marketplace.top_search'))
                        ->required(fn ($get) => $get('marketplace.top_search')),
                    Forms\Components\Toggle::make('marketplace.top_category')
                        ->label('أعلى التصنيف')
                        ->live(),
                    Forms\Components\DatePicker::make('marketplace.top_category_until')
                        ->label('أعلى التصنيف حتى')
                        ->visible(fn ($get) => $get('marketplace.top_category'))
                        ->required(fn ($get) => $get('marketplace.top_category')),
                    Forms\Components\Toggle::make('marketplace.top_subcategory')
                        ->label('أعلى الفئة الفرعية')
                        ->live(),
                    Forms\Components\DatePicker::make('marketplace.top_subcategory_until')
                        ->label('أعلى الفئة الفرعية حتى')
                        ->visible(fn ($get) => $get('marketplace.top_subcategory'))
                        ->required(fn ($get) => $get('marketplace.top_subcategory')),
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

    protected static function accountData(array $data): array
    {
        return collect($data)
            ->only(['name', 'email', 'phone', 'password', 'is_active', 'is_suspended'])
            ->filter(fn ($value, string $key) => $key !== 'password' || filled($value))
            ->all();
    }

    protected static function profileSlug(User $record, array $profileData): string
    {
        $base = Str::slug($profileData['business_name'] ?? $record->name) ?: 'provider-'.$record->id;
        $slug = $base;
        $counter = 2;

        while (Profile::query()
            ->where('slug', $slug)
            ->where('user_id', '!=', $record->id)
            ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected static function saveProviderSubscription(User $record, array $subscriptionData): void
    {
        if (! filled($subscriptionData['plan_id'] ?? null)) {
            return;
        }

        if (filled($subscriptionData['id'] ?? null)) {
            Subscription::query()
                ->where('user_id', $record->id)
                ->findOrFail($subscriptionData['id'])
                ->update([
                    'ends_at' => $subscriptionData['ends_at'],
                    'is_active' => $subscriptionData['is_active'] ?? false,
                    'notes' => $subscriptionData['notes'] ?? null,
                ]);

            return;
        }

        app(SubscriptionValidationService::class)->createForProvider($record, [
            'plan_id' => $subscriptionData['plan_id'],
            'starts_at' => $subscriptionData['starts_at'],
            'ends_at' => $subscriptionData['ends_at'],
            'notes' => $subscriptionData['notes'] ?? null,
        ]);
    }

    protected static function validateSubscriptionData(User $record, array $subscription): void
    {
        if (! filled($subscription['plan_id'] ?? null)) {
            return;
        }

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

    protected static function validateMarketplaceData(array $marketplace): void
    {
        foreach ([
            'homepage_featured' => 'homepage_featured_until',
            'top_search' => 'top_search_until',
            'top_category' => 'top_category_until',
            'top_subcategory' => 'top_subcategory_until',
        ] as $enabledField => $untilField) {
            if (! ($marketplace[$enabledField] ?? false)) {
                continue;
            }

            if (! filled($marketplace[$untilField] ?? null)) {
                throw ValidationException::withMessages([
                    "marketplace.{$untilField}" => 'تاريخ الانتهاء مطلوب عند تفعيل هذا الموضع.',
                ]);
            }

            if (Carbon::parse($marketplace[$untilField])->endOfDay()->isPast()) {
                throw ValidationException::withMessages([
                    "marketplace.{$untilField}" => 'يجب أن يكون تاريخ انتهاء الموضع في المستقبل.',
                ]);
            }
        }
    }
}
