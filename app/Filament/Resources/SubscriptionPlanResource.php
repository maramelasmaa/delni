<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\SubscriptionPlan;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    use AdminAccessOnly;

    protected static ?string $model = SubscriptionPlan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 21;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.billing');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.subscription_plan_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.subscription_plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.subscription_plan_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('إعداد الخطة')
                    ->tabs([
                        Tabs\Tab::make('الأساسيات')
                            ->schema([
                                Section::make('تفاصيل الخطة')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('اسم الخطة بالإنجليزية')
                                            ->placeholder('Starter Plan')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('name_ar')
                                            ->label('اسم الخطة بالعربية')
                                            ->placeholder('مثال: خطة البداية')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('tier')
                                            ->label('المستوى')
                                            ->placeholder('اختر المستوى')
                                            ->options([
                                                'basic' => 'أساسي',
                                                'standard' => 'قياسي',
                                                'premium' => 'مميز',
                                                'enterprise' => 'مؤسسات',
                                            ])
                                            ->required(),
                                    ]),

                                Section::make('التسعير')
                                    ->schema([
                                        Forms\Components\TextInput::make('price_lyd')
                                            ->label('السعر (د.ل)')
                                            ->placeholder('99.99')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required(),
                                        Forms\Components\TextInput::make('duration_months')
                                            ->label('المدة بالأشهر')
                                            ->placeholder('12')
                                            ->numeric()
                                            ->minValue(1)
                                            ->required(),
                                    ])
                                    ->columns(2),

                                Section::make('الحالة')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label(__('filament.fields.active'))
                                            ->default(true),
                                    ]),
                            ]),

                        Tabs\Tab::make('المزايا')
                            ->schema([
                                Section::make('رصيد التمييز')
                                    ->schema([
                                        Forms\Components\TextInput::make('featured_days_per_subscription')
                                            ->label('أيام التمييز لكل اشتراك')
                                            ->helperText('عدد الأيام التي يستطيع فيها مقدم الخدمة تمييز ملفه خلال الاشتراك')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                    ]),

                                Section::make('مزايا الظهور')
                                    ->schema([
                                        Forms\Components\Toggle::make('includes_homepage_featured')
                                            ->label('يشمل التمييز في الصفحة الرئيسية'),
                                        Forms\Components\Toggle::make('includes_top_search')
                                            ->label('يشمل موضع أعلى البحث'),
                                        Forms\Components\Toggle::make('includes_category_spotlight')
                                            ->label('يشمل تمييز التصنيف'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('tier')
                    ->colors([
                        'gray' => 'basic',
                        'info' => 'standard',
                        'success' => 'premium',
                        'danger' => 'enterprise',
                    ]),
                Tables\Columns\TextColumn::make('price_lyd')
                    ->label('السعر (د.ل)')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_months')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => "{$state} شهر")
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.fields.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('featured_days_per_subscription')
                    ->label('أيام التمييز')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tier')
                    ->options([
                        'basic' => 'أساسي',
                        'standard' => 'قياسي',
                        'premium' => 'مميز',
                        'enterprise' => 'مؤسسات',
                    ]),
            ])
            ->paginated([25, 50, 100])
            ->recordActions([
                EditAction::make()
                    ->modal(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => SubscriptionPlanResource\Pages\ListSubscriptionPlans::route('/'),
            'create' => SubscriptionPlanResource\Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => SubscriptionPlanResource\Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
