<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Schemas;

use App\Enums\BannerLinkType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الصورة')
                    ->schema([
                        FileUpload::make('image')
                            ->label('صورة البنر')
                            ->helperText('سيتم اقتصاص الصورة بنسبة 2:1 لضمان توحيد حجم جميع البنرات.')
                            ->image()
                            ->imageEditor()
                            // Locked to a single 2:1 ratio so every banner is identical.
                            // MUST match BANNER_RATIO in components/home/BannerCarousel.tsx
                            ->imageEditorAspectRatioOptions([
                                '2:1',
                            ])
                            ->imageEditorViewportWidth(800)
                            ->imageEditorViewportHeight(400)
                            ->disk('public')
                            ->directory('banners')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->imagePreviewHeight('220')
                            ->previewable()
                            ->openable()
                            ->downloadable()
                            ->maxSize(4096)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('النصوص')
                    ->schema([
                        TextInput::make('title')
                            ->label('العنوان')
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->label('النص التوضيحي')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('الرابط')
                    ->schema([
                        Select::make('link_type')
                            ->label('نوع الرابط')
                            ->options(collect(BannerLinkType::cases())->mapWithKeys(
                                fn (BannerLinkType $type) => [$type->value => $type->label()]
                            ))
                            ->default(BannerLinkType::None->value)
                            ->live()
                            ->required(),
                        TextInput::make('link_value')
                            ->label(fn (Get $get): string => match ($get('link_type')) {
                                BannerLinkType::Category->value => 'Slug التصنيف',
                                BannerLinkType::Provider->value => 'Slug مقدم الخدمة',
                                BannerLinkType::Url->value => 'الرابط الخارجي',
                                default => 'القيمة',
                            })
                            ->visible(fn (Get $get): bool => $get('link_type') !== BannerLinkType::None->value)
                            ->maxLength(500),
                    ])
                    ->columns(2),

                Section::make('الإعدادات')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('يبدأ في')
                                    ->nullable(),
                                DateTimePicker::make('ends_at')
                                    ->label('ينتهي في')
                                    ->nullable()
                                    ->after('starts_at'),
                            ]),
                    ]),
            ]);
    }
}
