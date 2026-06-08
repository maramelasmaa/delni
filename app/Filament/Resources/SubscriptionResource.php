<?php

namespace App\Filament\Resources;

use App\Models\Subscription;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.billing');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.subscription_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.subscription_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament.sections.subscription_details'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.fields.provider_user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->columnSpanFull()
                            ->hiddenOn('edit'),
                        Forms\Components\Select::make('plan_id')
                            ->label(__('filament.fields.plan'))
                            ->relationship('plan', 'name', fn ($query) => $query->where('is_active', true))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->localized_name)
                            ->required()
                            ->hiddenOn('edit'),
                        Forms\Components\DatePicker::make('starts_at')
                            ->label(__('filament.fields.started_at'))
                            ->required()
                            ->hiddenOn('edit'),
                        Forms\Components\DatePicker::make('ends_at')
                            ->label(__('filament.fields.ends_at'))
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('الحالة')
                    ->visibleOn('edit')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Placeholder::make('is_active')
                            ->label(__('filament.fields.active'))
                            ->content(fn ($record) => $record?->is_active ? 'نعم (نشط)' : 'لا (منتهي الصلاحية)'),
                        Forms\Components\Placeholder::make('approved_at')
                            ->label('تم التفعيل في')
                            ->content(fn ($record) => $record?->approved_at?->format('Y-m-d H:i') ?? 'غير متوفر'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.fields.provider'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan.localized_name')
                    ->label(__('filament.fields.plan'))
                    ->state(fn ($record) => $record->plan->localized_name)
                    ->sortable('plan.name'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('filament.fields.active'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('filament.fields.started_at'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('filament.fields.ends_at'))
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('approved_at')
                    ->label(__('filament.fields.approved'))
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->approved_at !== null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query) => $query->where('is_active', true))
                    ->label(__('filament.filters.active')),
                Tables\Filters\Filter::make('approved')
                    ->query(fn (Builder $query) => $query->whereNotNull('approved_at'))
                    ->label(__('filament.filters.approved')),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query) => $query->whereDate('ends_at', '<', now()))
                    ->label(__('filament.filters.expired')),
            ])
            ->paginated([25, 50, 100])
            ->recordActions([
                EditAction::make()
                    ->modal()
                    ->modalHeading('تعديل الاشتراك'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'plan']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => SubscriptionResource\Pages\ListSubscriptions::route('/'),
            'create' => SubscriptionResource\Pages\CreateSubscription::route('/create'),
            'edit' => SubscriptionResource\Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
