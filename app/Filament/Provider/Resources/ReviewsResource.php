<?php

namespace App\Filament\Provider\Resources;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class ReviewsResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'تقييماتي';

    protected static ?string $modelLabel = 'تقييم';

    protected static ?string $pluralModelLabel = 'التقييمات';

    protected static ?int $navigationSort = 6;

    protected static bool $shouldRegisterNavigation = true;

    /**
     * Reviews are read-only — provider cannot create
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Reviews are read-only — provider cannot edit
     */
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    /**
     * Reviews are read-only — provider cannot delete
     */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('تفاصيل التقييم')
                ->description('معلومات التقييم')
                ->schema([
                    Forms\Components\Placeholder::make('user.name')
                        ->label('من')
                        ->content(fn ($record) => $record->user?->name ?? 'مستخدم محذوف'),

                    Forms\Components\Placeholder::make('rating')
                        ->label('التقييم')
                        ->content(function ($record) {
                            $stars = str_repeat('⭐', (int) $record->rating);
                            $empty = str_repeat('☆', 5 - (int) $record->rating);

                            return "$stars$empty ({$record->rating}/5)";
                        }),

                    Forms\Components\Placeholder::make('comment')
                        ->label('التعليق')
                        ->content(fn ($record) => $record->comment ?? 'لا يوجد تعليق'),

                    Forms\Components\Placeholder::make('created_at')
                        ->label('التاريخ')
                        ->content(fn ($record) => $record->created_at?->format('d/m/Y H:i') ?? '-'),

                    Forms\Components\Placeholder::make('status')
                        ->label('حالة الموافقة')
                        ->content(fn (Review $record): string => static::formatReviewStatusForProvider($record, withIcon: true)),
                    Forms\Components\Placeholder::make('flagged_reason')
                        ->label('سبب البلاغ')
                        ->content(fn ($record) => $record?->flagged_by === auth()->id() ? ($record->flagged_reason ?? '-') : '-'),
                    Forms\Components\Placeholder::make('flag_review_response')
                        ->label('رد الإدارة على البلاغ')
                        ->content(fn (Review $record): string => static::formatFlagResponseForProvider($record)),
                    Forms\Components\Placeholder::make('moderation_note')
                        ->label('سبب القرار')
                        ->content(fn (Review $record): string => static::formatModerationNoteForProvider($record))
                        ->columnSpanFull(),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('من')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->user?->name ?? 'محذوف'),

                Tables\Columns\TextColumn::make('rating')
                    ->label('التقييم')
                    ->sortable()
                    ->getStateUsing(fn ($record) => str_repeat('⭐', (int) $record->rating)),

                Tables\Columns\TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(50)
                    ->getStateUsing(fn ($record) => $record->comment ?? '-'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (Review $record) => match (static::resolveReviewStatusValue($record)) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (Review $record): string => static::formatReviewStatusForProvider($record)),
                Tables\Columns\TextColumn::make('flag_response')
                    ->label('رد الإدارة')
                    ->getStateUsing(fn (Review $record): string => static::formatFlagResponseForProvider($record))
                    ->badge()
                    ->color(fn (Review $record): string => match (static::resolveFlagResponseState($record)) {
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('moderation_note')
                    ->label('سبب القرار')
                    ->getStateUsing(fn (Review $record): string => static::formatModerationNoteForProvider($record))
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                Action::make('flag')
                    ->label('الإبلاغ عن التقييم')
                    ->icon('heroicon-o-flag')
                    ->color('warning')
                    ->visible(fn (Review $record): bool => ! $record->is_flagged
                        && $record->profile
                        && $record->profile->user_id === auth()->id()
                    )
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('سبب البلاغ')
                            ->required()
                            ->minLength(10)
                            ->maxLength(1000)
                            ->rows(5)
                            ->placeholder('اشرح سبب الإبلاغ عن هذا التقييم'),
                    ])
                    ->action(function (Review $record, array $data): void {
                        Gate::authorize('flag', $record);

                        $record->update([
                            'is_flagged' => true,
                            'flagged_by' => auth()->id(),
                            'flagged_at' => now(),
                            'flagged_reason' => $data['reason'],
                            'flag_handled_at' => null,
                            'flag_handled_by' => null,
                        ]);

                        Notification::make()
                            ->title('تم إرسال البلاغ')
                            ->body('تم إرسال البلاغ للإدارة لمراجعته.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('الإبلاغ عن تقييم')
                    ->modalDescription('سيتم إرسال البلاغ للإدارة لمراجعته.')
                    ->modalSubmitActionLabel('إرسال البلاغ'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Provider sees only reviews on their own profile
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'profile'])
            ->whereHas('profile', fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    private static function resolveFlagResponseState(Review $record): ?string
    {
        if ((int) $record->flagged_by !== (int) auth()->id()) {
            return null;
        }

        if ($record->flag_handled_at === null) {
            return 'pending';
        }

        return $record->is_flagged ? 'accepted' : 'rejected';
    }

    private static function formatFlagResponseForProvider(Review $record): string
    {
        return match (static::resolveFlagResponseState($record)) {
            'pending' => 'بانتظار رد الإدارة',
            'accepted' => 'تم قبول البلاغ',
            'rejected' => 'تم رفض البلاغ',
            default => '-',
        };
    }

    private static function formatModerationNoteForProvider(Review $record): string
    {
        if ((int) $record->flagged_by !== (int) auth()->id()) {
            return '-';
        }

        return $record->moderation_note ?: ($record->flag_handled_at ? 'لا توجد ملاحظات إضافية.' : '-');
    }

    private static function resolveReviewStatusValue(Review $record): string
    {
        return $record->status instanceof \BackedEnum
            ? $record->status->value
            : (string) $record->status;
    }

    private static function formatReviewStatusForProvider(Review $record, bool $withIcon = false): string
    {
        return match (static::resolveReviewStatusValue($record)) {
            'approved' => $withIcon ? '✅ موافق عليه' : 'موافق',
            'pending' => $withIcon ? '⏳ قيد الانتظار' : 'قيد الانتظار',
            'rejected' => $withIcon ? '❌ مرفوض' : 'مرفوض',
            default => static::resolveReviewStatusValue($record),
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => ReviewsResource\Pages\ListReviews::route('/'),
        ];
    }
}
