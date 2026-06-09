<?php

namespace App\Filament\Provider\Resources;

use App\Models\ProviderCredential;
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

class CredentialsResource extends Resource
{
    protected static ?string $model = ProviderCredential::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'شهاداتي وخبراتي';

    protected static ?string $modelLabel = 'بيانات اعتماد';

    protected static ?string $pluralModelLabel = 'بيانات الاعتماد';

    protected static ?int $navigationSort = 4;  // unchanged

    protected static bool $shouldRegisterNavigation = true;

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole('provider') && $user->profile !== null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات الاعتماد')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('اسم بيانات الاعتماد')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('issuer')
                        ->label('جهة الإصدار')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('issue_date')
                        ->label('تاريخ الإصدار')
                        ->required(),
                    Forms\Components\TextInput::make('verification_url')
                        ->label('رابط التحقق (اختياري)')
                        ->url()
                        ->maxLength(500),
                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات إضافية')
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('معلومات النظام')
                ->description('للقراءة فقط')
                ->schema([
                    Forms\Components\Placeholder::make('created_at')
                        ->label('تاريخ الإنشاء')
                        ->content(fn ($record) => $record?->created_at?->format('d/m/Y H:i')),
                ])
                ->visible(fn ($record) => $record !== null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('issuer')
                    ->label('جهة الإصدار')
                    ->searchable(),
                Tables\Columns\TextColumn::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('issue_date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('profile', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return [
            'index' => CredentialsResource\Pages\ListCredentials::route('/'),
            'create' => CredentialsResource\Pages\CreateCredentials::route('/create'),
            'edit' => CredentialsResource\Pages\EditCredentials::route('/{record}/edit'),
        ];
    }
}
