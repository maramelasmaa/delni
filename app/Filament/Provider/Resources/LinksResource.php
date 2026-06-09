<?php

namespace App\Filament\Provider\Resources;

use App\Models\ContactInfo;
use App\Models\ProviderLink;
use App\Rules\SafeExternalUrl;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LinksResource extends Resource
{
    protected static ?string $model = ProviderLink::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'الروابط';

    protected static ?string $modelLabel = 'رابط';

    protected static ?string $pluralModelLabel = 'الروابط';

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات الرابط')
                ->schema([
                    Forms\Components\TextInput::make('label')
                        ->label('اسم الرابط')
                        ->required()
                        ->maxLength(255)
                        ->regex('/^[^\<\>\"]+$/')
                        ->validationMessages([
                            'regex' => 'لا يمكن استخدام أحرف HTML في اسم الرابط.',
                        ]),
                    Forms\Components\TextInput::make('url')
                        ->label('الرابط')
                        ->required()
                        ->url()
                        ->rules([new SafeExternalUrl])
                        ->maxLength(500),
                    Forms\Components\Select::make('type')
                        ->label('نوع الرابط')
                        ->options([
                            'website' => 'موقع إلكتروني',
                            'portfolio' => 'معرض أعمال',
                            'social' => 'وسائل اجتماعية',
                            'contact' => 'تواصل',
                            'other' => 'آخر',
                        ])
                        ->default('other')
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('الرابط')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab()
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'website' => 'موقع إلكتروني',
                        'portfolio' => 'معرض أعمال',
                        'social' => 'وسائل اجتماعية',
                        'contact' => 'تواصل',
                        default => 'آخر',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
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

    public static function getPages(): array
    {
        return [
            'index' => LinksResource\Pages\ListLinks::route('/'),
            'create' => LinksResource\Pages\CreateLinks::route('/create'),
            'edit' => LinksResource\Pages\EditLinks::route('/{record}/edit'),
        ];
    }

    public static function getContactInfo()
    {
        return ContactInfo::instance();
    }

    public static function getSupportMessage(): string
    {
        $contact = static::getContactInfo();

        if ($contact->phone) {
            return "يرجى التواصل مع الدعم الفني على: {$contact->phone}";
        }

        return 'يرجى التواصل مع الدعم الفني لمساعدتك.';
    }
}
