<?php

namespace App\Filament\Provider\Resources;

use App\Models\Subscription;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'الاشتراك';

    protected static ?string $modelLabel = 'اشتراك';

    protected static ?string $pluralModelLabel = 'الاشتراكات';

    protected static bool $shouldRegisterNavigation = true;

    /**
     * Subscription is read-only — provider cannot create
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Subscription is read-only — provider cannot edit
     */
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    /**
     * Subscription is read-only — provider cannot delete
     */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات الاشتراك')
                ->description('تفاصيل خطة اشتراكك الحالية')
                ->schema([
                    Tables\Columns\TextColumn::make('plan.name')
                        ->label('اسم الخطة')
                        ->getStateUsing(fn ($record) => $record->plan?->name ?? '-'),

                    Tables\Columns\TextColumn::make('status')
                        ->label('الحالة')
                        ->getStateUsing(function ($record) {
                            return match ($record->status) {
                                'active' => '🟢 نشط',
                                'expired' => '🔴 منتهي',
                                'cancelled' => '⚫ ملغى',
                                default => $record->status,
                            };
                        }),

                    Tables\Columns\TextColumn::make('started_at')
                        ->label('تاريخ البدء')
                        ->date('d/m/Y')
                        ->getStateUsing(fn ($record) => $record->started_at?->format('d/m/Y') ?? '-'),

                    Tables\Columns\TextColumn::make('expires_at')
                        ->label('تاريخ الانتهاء')
                        ->date('d/m/Y')
                        ->getStateUsing(fn ($record) => $record->expires_at?->format('d/m/Y') ?? '-'),

                    Tables\Columns\TextColumn::make('plan.benefits')
                        ->label('المميزات')
                        ->getStateUsing(function ($record) {
                            $benefits = $record->plan?->benefits ?? [];
                            if (is_array($benefits) && ! empty($benefits)) {
                                return implode(', ', $benefits);
                            }

                            return '-';
                        }),
                ])
                ->columns(1),

            Section::make('حالة الظهور')
                ->description('حالة ظهورك في منصة دلني')
                ->schema([
                    Tables\Columns\TextColumn::make('is_featured')
                        ->label('ظهور مميز')
                        ->getStateUsing(fn ($record) => $record->plan?->is_featured ? '✅ مُفعّل' : '❌ غير مفعّل'),

                    Tables\Columns\TextColumn::make('featured_until')
                        ->label('الظهور المميز حتى')
                        ->date('d/m/Y')
                        ->getStateUsing(fn ($record) => $record->plan?->is_featured && $record->expires_at
                            ? $record->expires_at->format('d/m/Y')
                            : '-'),
                ])
                ->columns(1),

            Section::make('ملاحظة مهمة')
                ->description('لتجديد اشتراكك أو تغيير خطتك، يرجى التواصل معنا')
                ->schema([
                    Tables\Columns\TextColumn::make('help')
                        ->label('')
                        ->getStateUsing(fn () => '📞 يمكنك التواصل مع فريق الدعم للمزيد من المعلومات'),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('الخطة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'expired',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغى',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('الانتهاء')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([]);
    }

    /**
     * Provider sees only their own subscriptions
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('user', fn (Builder $query) => $query->where('id', auth()->id()));
    }

    public static function getPages(): array
    {
        return [
            'index' => SubscriptionResource\Pages\ListSubscriptions::route('/'),
        ];
    }
}
