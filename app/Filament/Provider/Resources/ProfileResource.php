<?php

namespace App\Filament\Provider\Resources;

use App\Models\City;
use App\Models\Profile;
use App\Models\ProviderType;
use App\Models\Subcategory;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'ملفي التجاري';

    protected static ?string $modelLabel = 'الملف الشخصي';

    protected static ?string $pluralModelLabel = 'الملفات الشخصية';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationUrl(): string
    {
        $profile = auth()->user()?->profile;

        if ($profile) {
            return static::getUrl('edit', ['record' => $profile->id]);
        }

        return static::getUrl('create');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole('provider') && $user->profile === null;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('الأساسيات')
                ->description('معلومات مقدم الخدمة الأساسية')
                ->schema([
                    Forms\Components\TextInput::make('business_name')
                        ->label('اسم العمل')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('provider_type')
                        ->label('نوع العمل')
                        ->options(fn () => ProviderType::options(activeOnly: true))
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('category_id')
                        ->label('التصنيف الرئيسي')
                        ->relationship('category', 'name', fn (Builder $query) => $query->where('is_active', true))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            $set('subcategories', []);
                        }),
                    Forms\Components\Select::make('subcategories')
                        ->label('التصنيفات الفرعية')
                        ->relationship('subcategories', 'name')
                        ->multiple()
                        ->options(function ($get) {
                            $categoryId = $get('category_id');
                            if (! $categoryId) {
                                return [];
                            }

                            return Subcategory::where('category_id', $categoryId)
                                ->where('is_active', true)
                                ->pluck('name', 'id');
                        })
                        ->searchable(),
                    Forms\Components\Select::make('city_id')
                        ->label('المدينة')
                        ->options(fn () => City::where('is_active', true)->pluck('name_ar', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->columns(2),

            Section::make('عن العمل')
                ->description('تفاصيل العمل والخدمات')
                ->schema([
                    Forms\Components\Textarea::make('bio')
                        ->label('الوصف')
                        ->rows(3)
                        ->columnSpanFull()
                        ->maxLength(500),
                    Forms\Components\Toggle::make('offers_remote_work')
                        ->label('نقدم خدمات العمل من المنزل')
                        ->inline(false),
                    Forms\Components\Textarea::make('service_area_note')
                        ->label('ملاحظات نطاق الخدمة')
                        ->rows(2)
                        ->columnSpanFull()
                        ->maxLength(500),
                ])
                ->columns(2),

            Section::make('وسائل التواصل والمواقع')
                ->description('روابط مواقعك والمنصات الإجتماعية')
                ->schema([
                    Forms\Components\TextInput::make('phone')
                        ->label('الهاتف')
                        ->tel()
                        ->required()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('whatsapp')
                        ->label('واتساب')
                        ->tel()
                        ->required()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('website')
                        ->label('الموقع الإلكتروني')
                        ->url()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('instagram')
                        ->label('إنستاجرام')
                        ->url()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('facebook')
                        ->label('فيسبوك')
                        ->url()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('linkedin')
                        ->label('لينكد إن')
                        ->url()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('github')
                        ->label('جيتهاب')
                        ->url()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('map_url')
                        ->label('رابط الخريطة')
                        ->url()
                        ->maxLength(500),
                ])
                ->columns(2),

            Section::make('الصور')
                ->description('شعارك وصورة الغلاف')
                ->schema([
                    Forms\Components\FileUpload::make('logo')
                        ->label('الشعار')
                        ->image()
                        ->maxSize(5120)
                        ->directory('profiles/logos')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('صورة الغلاف')
                        ->image()
                        ->maxSize(5120)
                        ->directory('profiles/covers')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                ])
                ->columns(2),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->label('اسم العمل')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => ProfileResource\Pages\ListProfiles::route('/'),
            'create' => ProfileResource\Pages\CreateProfile::route('/create'),
            'edit' => ProfileResource\Pages\EditProfile::route('/{record}/edit'),
        ];
    }
}
