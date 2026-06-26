<?php

namespace App\Filament\Provider\Resources;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProviderType;
use App\Models\Subcategory;
use App\Rules\SafeExternalUrl;
use App\Rules\SocialProfileReference;
use App\Services\ProfileImageService;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'ملفي التجاري';

    protected static ?string $modelLabel = 'الملف الشخصي';

    protected static ?string $pluralModelLabel = 'الملفات الشخصية';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $recordRouteKeyName = 'slug';

    public static function getNavigationUrl(): string
    {
        $profile = auth()->user()?->profile;

        if ($profile) {
            return static::getUrl('edit', ['record' => $profile->slug], panel: 'provider');
        }

        return static::getUrl('create', panel: 'provider');
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
                        ->label('اسم النشاط التجاري')
                        ->placeholder('مثال: شركة الأمان للصيانة والتكييف')
                        ->helperText('الاسم الذي سيظهر للعملاء في الموقع ونتائج البحث.')
                        ->required()
                        ->maxLength(500),
                    Forms\Components\Select::make('provider_type')
                        ->label('نوع النشاط')
                        ->placeholder('اختر نوع النشاط')
                        ->helperText('ساعد العملاء في إيجادك بسهولة بتحديد نوع نشاطك.')
                        ->options(fn () => ProviderType::options(activeOnly: true))
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('category_id')
                        ->label('التصنيف الرئيسي')
                        ->placeholder('اختر التصنيف الأنسب لنشاطك')
                        ->helperText('اختر التصنيف الأساسي الذي يعكس طبيعة عملك.')
                        ->options(fn () => Category::query()
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name_ar')
                            ->get()
                            ->mapWithKeys(fn (Category $category): array => [
                                $category->id => $category->localized_name,
                            ])
                            ->all())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            $set('subcategories', []);
                        }),
                    Forms\Components\Select::make('subcategories')
                        ->label('التصنيفات الفرعية')
                        ->placeholder('اختر التصنيفات التي تصف خدماتك بدقة')
                        ->helperText('يمكنك اختيار عدة تصنيفات فرعية لتوضيح تخصصاتك.')
                        ->relationship(
                            'subcategories',
                            'name',
                            fn (Builder $query, Get $get): Builder => $query
                                ->when(
                                    $get('category_id'),
                                    fn (Builder $query, int|string $categoryId): Builder => $query->where('category_id', $categoryId),
                                    fn (Builder $query): Builder => $query->whereRaw('1 = 0'),
                                )
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->orderBy('name_ar')
                        )
                        ->getOptionLabelFromRecordUsing(fn (Subcategory $record): string => $record->localized_name)
                        ->multiple()
                        ->required()
                        ->searchable(['name', 'name_ar'])
                        ->preload()
                        ->live(),
                    Forms\Components\Select::make('city_id')
                        ->label('المدينة')
                        ->placeholder('اختر المدينة')
                        ->helperText('حدد المدينة الرئيسية لعملك ليسهل على العملاء البحث عنك.')
                        ->options(fn () => City::where('is_active', true)->pluck('name_ar', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->columns(2),

            Section::make('عن النشاط')
                ->description('تفاصيل العمل والخدمات')
                ->schema([
                    Forms\Components\Textarea::make('bio')
                        ->label('نبذة عن النشاط')
                        ->placeholder('اكتب نبذة مختصرة توضح خدماتك وخبرتك وما يميز نشاطك...')
                        ->helperText('كلما كانت النبذة واضحة زادت ثقة العملاء بك. (الحد الأقصى 500 حرف)')
                        ->rows(3)
                        ->columnSpanFull()
                        ->maxLength(500),
                    Forms\Components\TextInput::make('experience_years')
                        ->label('سنوات الخبرة')
                        ->placeholder('مثال: 5')
                        ->helperText('عدد سنوات خبرتك في هذا المجال.')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                    Forms\Components\Toggle::make('offers_remote_work')
                        ->label('تقديم خدمات عن بعد')
                        ->helperText('فعّل هذا الخيار إذا كنت تقدم خدمات أونلاين أو عن بعد.')
                        ->inline(false),
                ])
                ->columns(2),

            Section::make('وسائل التواصل')
                ->description('روابط مواقعك والمنصات الاجتماعية')
                ->schema([
                    Forms\Components\TextInput::make('phone')
                        ->label('رقم الهاتف')
                        ->placeholder('2180912345678')
                        ->helperText('يفضل استخدام رقم متاح للعملاء بشكل دائم.')
                        ->tel()
                        ->required()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('whatsapp')
                        ->label('واتساب')
                        ->placeholder('2180912345678')
                        ->helperText('أدخل الرقم مع مفتاح ليبيا حتى يتمكن العملاء من التواصل معك مباشرة عبر واتساب.')
                        ->tel()
                        ->required()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('website')
                        ->label('الموقع الإلكتروني')
                        ->placeholder('https://example.com')
                        ->helperText('اتركه فارغاً إذا لم يكن لديك موقع إلكتروني.')
                        ->url()
                        ->rules([new SafeExternalUrl])
                        ->maxLength(255),
                    Forms\Components\TextInput::make('instagram_handle')
                        ->label('إنستاجرام')
                        ->placeholder('https://instagram.com/...')
                        ->helperText('رابط صفحتك على إنستاجرام (اختياري)')
                        ->rules([new SocialProfileReference('instagram')])
                        ->maxLength(255),
                    Forms\Components\TextInput::make('facebook_slug')
                        ->label('فيسبوك')
                        ->placeholder('https://facebook.com/...')
                        ->helperText('رابط صفحتك على فيسبوك (اختياري)')
                        ->rules([new SocialProfileReference('facebook')])
                        ->maxLength(255),
                    Forms\Components\TextInput::make('linkedin_slug')
                        ->label('لينكد إن')
                        ->placeholder('https://linkedin.com/...')
                        ->helperText('رابط ملفك على لينكد إن (اختياري)')
                        ->rules([new SocialProfileReference('linkedin')])
                        ->maxLength(255),
                    Forms\Components\TextInput::make('github_username')
                        ->label('جيتهاب')
                        ->placeholder('https://github.com/...')
                        ->helperText('رابط حسابك على جيتهاب (اختياري)')
                        ->rules([new SocialProfileReference('github')])
                        ->maxLength(255),
                    Forms\Components\TextInput::make('map_url')
                        ->label('رابط موقعك على الخريطة')
                        ->placeholder('https://maps.google.com/...')
                        ->helperText('يساعد العملاء في الوصول إلى موقعك بسهولة.')
                        ->url()
                        ->rules([new SafeExternalUrl([
                            'google.com',
                            'maps.app.goo.gl',
                            'openstreetmap.org',
                            'maps.apple.com',
                        ])])
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('الصور')
                ->description('شعارك وصورة الغلاف')
                ->schema([
                    Forms\Components\FileUpload::make('logo')
                        ->label('شعار النشاط')
                        ->helperText('صورة مربعة (240×240 بكسل على الأقل). سيتم تحويلها تلقائياً إلى حجم مثالي. (الحد الأقصى 2 MB)')
                        ->image()
                        ->maxSize(2048)
                        ->imagePreviewHeight('400')
                        ->previewable()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->columnSpanFull()
                        ->required()
                        ->saveUploadedFileUsing(function (UploadedFile $file, Profile $record, ProfileImageService $imageService) {
                            return $imageService->replaceImage($record->logo, $file, 'avatar');
                        })
                        ->deleteUploadedFileUsing(function ($file, ProfileImageService $imageService) {
                            $imageService->deleteImage($file);
                        }),
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('صورة الغلاف')
                        ->helperText('صورة أفقية بنسبة 2:1 (1080×540 بكسل على الأقل). سيتم تحويلها تلقائياً إلى حجم مثالي. (الحد الأقصى 4 MB)')
                        ->image()
                        ->maxSize(4096)
                        ->imagePreviewHeight('300')
                        ->previewable()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->columnSpanFull()
                        ->required()
                        ->saveUploadedFileUsing(function (UploadedFile $file, Profile $record, ProfileImageService $imageService) {
                            return $imageService->replaceImage($record->cover_image, $file, 'cover');
                        })
                        ->deleteUploadedFileUsing(function ($file, ProfileImageService $imageService) {
                            $imageService->deleteImage($file);
                        }),
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
                Tables\Columns\TextColumn::make('provider_type.localized_name')
                    ->label('نوع النشاط')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.localized_name')
                    ->label('التصنيف الرئيسي')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.localized_name')
                    ->label('المدينة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->copyable(),
                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('واتساب')
                    ->limit(20),
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
