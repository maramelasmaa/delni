<?php

namespace App\Filament\Provider\Resources;

use App\Models\PortfolioItem;
use App\Services\ProfileImageService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class PortfolioResource extends Resource
{
    protected static ?string $model = PortfolioItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'أعمالي ومشاريعي';

    protected static ?string $modelLabel = 'عمل';

    protected static ?string $pluralModelLabel = 'الأعمال والمشاريع';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('تفاصيل المشروع')
                ->description('اعرض نماذج من أعمالك لزيادة ثقة العملاء')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('اسم المشروع')
                        ->placeholder('مثال: تشطيب فيلا سكنية')
                        ->helperText('اختر عنواناً واضحاً.')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط (مرئي للعملاء)')
                        ->helperText('أوقفه لإخفائه من ملفك.')
                        ->default(true)
                        ->inline(false)
                        ->columnSpanFull(),
                ]),

            Section::make('صور المشروع')
                ->description('أضف صوراً عالية الجودة لعرض أفضل للعملاء')
                ->schema([
                    Repeater::make('images')
                        ->relationship()
                        ->label('الصور')
                        ->schema([
                            Forms\Components\FileUpload::make('path')
                                ->label('الصورة')
                                ->helperText('صورة واضحة. الحد الأقصى 4 MB.')
                                ->image()
                                ->maxSize(4096)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->saveUploadedFileUsing(function (UploadedFile $file, ProfileImageService $imageService) {
                                    return $imageService->storePortfolioImage($file);
                                })
                                ->deleteUploadedFileUsing(function ($file, ProfileImageService $imageService) {
                                    $imageService->deleteImage($file);
                                })
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('alt')
                                ->label('النص البديل')
                                ->placeholder('وصف مختصر للصورة')
                                ->helperText('اختياري.')
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->minItems(1)
                        ->maxItems(4)
                        ->addActionLabel('إضافة صورة')
                        ->deleteAction(
                            fn (Action $action) => $action->label('حذف'),
                        )
                        ->helperText('حتى 4 صور لهذا المشروع.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('short_description')
                    ->label('الوصف المختصر')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
                Tables\Columns\TextColumn::make('images_count')
                    ->label('عدد الصور')
                    ->getStateUsing(fn ($record) => $record->images->count()),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('profile', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('provider')) {
            return false;
        }

        $profile = $user->profile;
        if (! $profile) {
            return false;
        }

        return $profile->portfolioItems()->count() < 2;
    }

    public static function getPages(): array
    {
        return [
            'index' => PortfolioResource\Pages\ListPortfolioItems::route('/'),
            'create' => PortfolioResource\Pages\CreatePortfolioItem::route('/create'),
            'edit' => PortfolioResource\Pages\EditPortfolioItem::route('/{record}/edit'),
        ];
    }
}
