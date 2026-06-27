<?php

namespace App\Filament\Resources\ContactInfos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactInfoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.sections.contact_info_section'))
                    ->schema([
                        TextInput::make('whatsapp')
                            ->label(__('filament.fields.whatsapp'))
                            ->placeholder(__('filament.placeholders.whatsapp'))
                            ->helperText(__('filament.help_text.whatsapp_format'))
                            ->required(),
                        TextInput::make('phone')
                            ->label(__('filament.fields.phone'))
                            ->placeholder(__('filament.placeholders.phone'))
                            ->helperText(__('filament.help_text.phone')),
                        TextInput::make('email')
                            ->label(__('filament.fields.email'))
                            ->email()
                            ->placeholder(__('filament.placeholders.email')),
                        TextInput::make('facebook')
                            ->label(__('filament.fields.facebook'))
                            ->url()
                            ->placeholder(__('filament.placeholders.facebook')),
                        TextInput::make('address')
                            ->label(__('filament.fields.address'))
                            ->placeholder(__('filament.placeholders.address'))
                            ->helperText(__('filament.help_text.address_optional')),
                    ]),
                Section::make('صفحة الترحيب')
                    ->schema([
                        TextInput::make('welcome_badge')
                            ->label('الشارة الصغيرة')
                            ->placeholder('مثال: تطبيق دلني قريباً'),
                        TextInput::make('welcome_title')
                            ->label('العنوان الرئيسي')
                            ->placeholder('مثال: دلني يقرّبك من الخدمة المناسبة بسرعة ووضوح.'),
                        Textarea::make('welcome_subtitle')
                            ->label('النص التعريفي')
                            ->placeholder('اكتب وصفاً قصيراً يظهر في الصفحة الأولى...')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('ios_app_url')
                            ->label('رابط App Store')
                            ->url()
                            ->placeholder('https://apps.apple.com/...'),
                        TextInput::make('android_app_url')
                            ->label('رابط Google Play')
                            ->url()
                            ->placeholder('https://play.google.com/store/apps/details?...'),
                    ])
                    ->columns(2),
            ]);
    }
}
