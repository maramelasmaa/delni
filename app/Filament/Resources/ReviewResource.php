<?php

namespace App\Filament\Resources;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Services\ReviewModerationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 40;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.community');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.review_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.review_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament.sections.review_info'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Placeholder::make('user_id')
                            ->label(__('filament.fields.reviewer'))
                            ->content(fn ($record) => $record?->user?->name ?? '—'),
                        Forms\Components\Placeholder::make('profile_id')
                            ->label(__('filament.fields.profile'))
                            ->content(fn ($record) => $record?->profile?->business_name ?? '—'),
                        Forms\Components\Placeholder::make('rating')
                            ->label(__('filament.fields.rating'))
                            ->content(fn ($record) => $record?->rating.'/5' ?? '—'),
                        Forms\Components\Placeholder::make('comment')
                            ->label(__('filament.fields.comment'))
                            ->content(fn ($record) => $record?->comment ?? '—')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('flagged_by')
                            ->label(__('filament.fields.flagged_by'))
                            ->content(fn ($record) => $record?->flaggedBy?->name ?? '—'),
                        Forms\Components\Placeholder::make('flagged_reason')
                            ->label(__('filament.fields.flagged_reason'))
                            ->content(fn ($record) => $record?->flagged_reason ?? '—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('filament.sections.review_moderation'))
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label(__('filament.fields.status'))
                            ->placeholder(__('filament.placeholders.review_decision'))
                            ->options([
                                ReviewStatus::PENDING->value => __('filament.status.pending'),
                                ReviewStatus::APPROVED->value => __('filament.status.approved'),
                                ReviewStatus::REJECTED->value => __('filament.status.rejected'),
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('moderation_note')
                            ->label(__('filament.fields.moderation_notes'))
                            ->placeholder(__('filament.placeholders.review_moderation_note'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.fields.reviewer'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.business_name')
                    ->label(__('filament.fields.profile'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')->label(__('filament.fields.rating'))->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.fields.status'))
                    ->badge()
                    ->color(fn (ReviewStatus $state): string => match ($state) {
                        ReviewStatus::PENDING => 'warning',
                        ReviewStatus::APPROVED => 'success',
                        ReviewStatus::REJECTED => 'danger',
                    }),
                Tables\Columns\IconColumn::make('is_flagged')
                    ->boolean()
                    ->label(__('filament.fields.flagged')),
                Tables\Columns\TextColumn::make('flagged_reason')
                    ->label(__('filament.fields.flagged_reason'))
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('flag_handled_at')
                    ->boolean()
                    ->label(__('filament.fields.flag_handled_at'))
                    ->getStateUsing(fn (Review $record): bool => $record->flag_handled_at !== null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('filament.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ReviewStatus::PENDING->value => __('filament.status.pending'),
                        ReviewStatus::APPROVED->value => __('filament.status.approved'),
                        ReviewStatus::REJECTED->value => __('filament.status.rejected'),
                    ]),
                Tables\Filters\Filter::make('flagged')
                    ->query(fn ($query) => $query->where('is_flagged', true))
                    ->label(__('filament.filters.flagged_only')),
                Tables\Filters\Filter::make('unhandled_flags')
                    ->query(fn ($query) => $query->where('is_flagged', true)->whereNull('flag_handled_at'))
                    ->label(__('filament.filters.unhandled_flags')),
                Tables\Filters\Filter::make('deleted')
                    ->query(fn ($query) => $query->onlyTrashed())
                    ->label(__('filament.filters.deleted_reviews')),
            ])
            ->paginated([25, 50, 100])
            ->recordActions([
                Action::make('acceptFlag')
                    ->label(__('filament.actions.accept_flag_hide_review'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.headings.accept_flag_confirmation'))
                    ->visible(fn (Review $record): bool => $record->is_flagged && $record->flag_handled_at === null && ! $record->trashed())
                    ->action(function (Review $record, ReviewModerationService $service): void {
                        $service->acceptFlag($record);
                    }),
                Action::make('rejectFlag')
                    ->label(__('filament.actions.reject_flag_keep_review'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.headings.reject_flag_confirmation'))
                    ->visible(fn (Review $record): bool => $record->is_flagged && $record->flag_handled_at === null && ! $record->trashed())
                    ->action(function (Review $record, ReviewModerationService $service): void {
                        $service->rejectFlag($record);
                    }),
                Action::make('approve')
                    ->label(__('filament.actions.approve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Review $record): bool => ! $record->is_flagged && $record->status === ReviewStatus::APPROVED->value && ! $record->trashed())
                    ->action(function (Review $record, ReviewModerationService $service): void {
                        $service->approve($record);
                    }),
                Action::make('reject')
                    ->label(__('filament.actions.reject_review'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Review $record): bool => ! $record->is_flagged && $record->status === ReviewStatus::APPROVED->value && ! $record->trashed())
                    ->action(function (Review $record, ReviewModerationService $service): void {
                        $service->reject($record);
                    }),
                EditAction::make()
                    ->modal(),
                DeleteAction::make()
                    ->visible(fn (Review $record): bool => ! $record->trashed()),
                RestoreAction::make()
                    ->visible(fn (Review $record): bool => $record->trashed()),
            ])
            ->groupedBulkActions([
                BulkAction::make('approve')
                    ->label(__('filament.actions.approve_selected'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records, ReviewModerationService $service): void {
                        $records->each(fn ($review) => $service->approve($review));
                    }),
                BulkAction::make('reject')
                    ->label(__('filament.actions.reject_selected'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records, ReviewModerationService $service): void {
                        $records->each(fn ($review) => $service->reject($review));
                    }),
                BulkAction::make('acceptFlags')
                    ->label(__('filament.actions.accept_selected_flags'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records, ReviewModerationService $service): void {
                        $records
                            ->filter(fn (Review $review): bool => $review->is_flagged && $review->flag_handled_at === null)
                            ->each(fn (Review $review) => $service->acceptFlag($review));
                    }),
                BulkAction::make('rejectFlags')
                    ->label(__('filament.actions.reject_selected_flags'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records, ReviewModerationService $service): void {
                        $records
                            ->filter(fn (Review $review): bool => $review->is_flagged && $review->flag_handled_at === null)
                            ->each(fn (Review $review) => $service->rejectFlag($review));
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed()->with(['user', 'profile', 'flaggedBy']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ReviewResource\Pages\ListReviews::route('/'),
            'edit' => ReviewResource\Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
