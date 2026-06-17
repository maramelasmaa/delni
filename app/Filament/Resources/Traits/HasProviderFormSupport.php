<?php

namespace App\Filament\Resources\Traits;

use App\Models\Profile;
use App\Models\ProviderType;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait HasProviderFormSupport
{
    protected static function accessSchema(): array
    {
        return [
            Section::make('صلاحية الظهور')
                ->description('يتحكم متى يظهر مقدم الخدمة على الموقع العام.')
                ->schema([
                    Forms\Components\DateTimePicker::make('profile.provider_access_ends_at')
                        ->label('تاريخ انتهاء الظهور')
                        ->helperText('لن يظهر مقدم الخدمة في الموقع العام بعد هذا التاريخ.')
                        ->nullable()
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
                        ->content(fn (?User $record) => $record?->profile?->subcategories?->pluck('localized_name')?->join(', ') ?: '-'),
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

    public static function accountData(array $data): array
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

    protected static function saveProviderAccess(Profile $profile, ?string $accessEndsAt): void
    {
        $profile->update([
            'provider_access_ends_at' => filled($accessEndsAt) ? Carbon::parse($accessEndsAt) : null,
        ]);
    }

    protected static function saveHomepagePlacement(Profile $profile, array $marketplaceData): void
    {
        $homepageFeatured = (bool) ($marketplaceData['homepage_featured'] ?? false);

        $profile->stats()->firstOrCreate(['profile_id' => $profile->id])->update([
            'is_homepage_featured' => $homepageFeatured,
            'homepage_featured_until' => $homepageFeatured
                ? ($marketplaceData['homepage_featured_until'] ?? null)
                : null,
            'is_top_search' => false,
            'top_search_until' => null,
            'is_top_category' => false,
            'top_category_until' => null,
            'is_top_subcategory' => false,
            'top_subcategory_until' => null,
        ]);
    }

    protected static function extendProviderAccess(User $record, int $days): void
    {
        $profile = $record->profile;

        if (! $profile) {
            Notification::make()
                ->title('لا يوجد ملف لمقدم الخدمة')
                ->danger()
                ->send();

            return;
        }

        $current = $profile->provider_access_ends_at;
        $base = $current && $current->isFuture() ? $current : Carbon::now();
        $newDate = $base->copy()->addDays($days);

        $profile->update(['provider_access_ends_at' => $newDate]);

        Notification::make()
            ->title('تم تمديد صلاحية الظهور')
            ->body('ينتهي الظهور في: '.$newDate->format('Y-m-d H:i'))
            ->success()
            ->send();
    }

    protected static function validateMarketplaceData(array $marketplace): void
    {
        foreach (['homepage_featured' => 'homepage_featured_until'] as $enabledField => $untilField) {
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
