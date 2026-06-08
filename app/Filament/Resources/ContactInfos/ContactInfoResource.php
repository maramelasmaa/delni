<?php

namespace App\Filament\Resources\ContactInfos;

use App\Filament\Resources\ContactInfos\Pages\CreateContactInfo;
use App\Filament\Resources\ContactInfos\Pages\EditContactInfo;
use App\Filament\Resources\ContactInfos\Pages\ListContactInfos;
use App\Filament\Resources\ContactInfos\Schemas\ContactInfoForm;
use App\Filament\Resources\ContactInfos\Tables\ContactInfosTable;
use App\Models\ContactInfo;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ContactInfoResource extends Resource
{
    protected static ?string $model = ContactInfo::class;

    public static function getNavigationLabel(): string
    {
        return __('filament.models.contact_info_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-phone';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.system');
    }

    public static function form(Schema $schema): Schema
    {
        return ContactInfoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactInfosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactInfos::route('/'),
            'create' => CreateContactInfo::route('/create'),
            'edit' => EditContactInfo::route('/{record}/edit'),
        ];
    }
}
