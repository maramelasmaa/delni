<?php

namespace App\Filament\Provider\Resources;

use App\Models\Review;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReviewsResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'التقييمات';

    protected static ?string $modelLabel = 'تقييم';

    protected static ?string $pluralModelLabel = 'التقييمات';

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
                    Tables\Columns\TextColumn::make('user.name')
                        ->label('من')
                        ->getStateUsing(fn ($record) => $record->user?->name ?? 'مستخدم محذوف'),

                    Tables\Columns\TextColumn::make('rating')
                        ->label('التقييم')
                        ->getStateUsing(function ($record) {
                            $stars = str_repeat('⭐', (int) $record->rating);
                            $empty = str_repeat('☆', 5 - (int) $record->rating);

                            return "$stars$empty ({$record->rating}/5)";
                        }),

                    Tables\Columns\TextColumn::make('comment')
                        ->label('التعليق')
                        ->getStateUsing(fn ($record) => $record->comment ?? 'لا يوجد تعليق'),

                    Tables\Columns\TextColumn::make('created_at')
                        ->label('التاريخ')
                        ->date('d/m/Y H:i')
                        ->getStateUsing(fn ($record) => $record->created_at?->format('d/m/Y H:i') ?? '-'),

                    Tables\Columns\TextColumn::make('status')
                        ->label('حالة الموافقة')
                        ->getStateUsing(function ($record) {
                            return match ($record->status) {
                                'approved' => '✅ موافق عليه',
                                'pending' => '⏳ قيد الانتظار',
                                'rejected' => '❌ مرفوض',
                                default => $record->status,
                            };
                        }),
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

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'approved' => 'موافق',
                        'pending' => 'قيد الانتظار',
                        'rejected' => 'مرفوض',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Provider sees only reviews on their own profile
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('profile', fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return [
            'index' => ReviewsResource\Pages\ListReviews::route('/'),
        ];
    }
}
