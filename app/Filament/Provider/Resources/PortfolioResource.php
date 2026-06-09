<?php

namespace App\Filament\Provider\Resources;

use App\Models\PortfolioItem;
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

class PortfolioResource extends Resource
{
    protected static ?string $model = PortfolioItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'الأعمال والمشاريع';

    protected static ?string $modelLabel = 'عمل';

    protected static ?string $pluralModelLabel = 'الأعمال والمشاريع';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('تفاصيل المشروع')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان المشروع')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('short_description')
                        ->label('وصف قصير')
                        ->rows(2)
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('الوصف التفصيلي')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('main_url')
                        ->label('رابط المشروع (اختياري)')
                        ->url()
                        ->maxLength(500),
                    Forms\Components\TextInput::make('link')
                        ->label('رابط إضافي (اختياري)')
                        ->url()
                        ->maxLength(500),
                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(2),

            Section::make('صور المشروع')
                ->description('أضف ما يصل إلى 4 صور لكل مشروع')
                ->schema([
                    Repeater::make('images')
                        ->relationship()
                        ->label('الصور')
                        ->schema([
                            Forms\Components\FileUpload::make('path')
                                ->label('الصورة')
                                ->image()
                                ->maxSize(5120)
                                ->directory('portfolio')
                                ->visibility('public')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->required(),
                            Forms\Components\TextInput::make('alt')
                                ->label('النص البديل')
                                ->maxLength(255),
                        ])
                        ->columns(1)
                        ->maxItems(4)
                        ->addActionLabel('إضافة صورة')
                        ->deleteAction(
                            fn (Action $action) => $action->label('حذف'),
                        ),
                ])
                ->columnSpanFull(),
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
        $profile = auth()->user()?->profile;
        if (! $profile) {
            return false;
        }

        // Check if user has reached max 2 portfolio items
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
