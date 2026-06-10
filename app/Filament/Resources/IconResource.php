<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IconResource\Pages\CreateIcon;
use App\Filament\Resources\IconResource\Pages\EditIcon;
use App\Filament\Resources\IconResource\Pages\ListIcons;
use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Models\Icon;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class IconResource extends Resource
{
    use AdminAccessOnly;

    protected static ?string $model = Icon::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 34;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.marketplace');
    }

    public static function getNavigationLabel(): string
    {
        return 'Icons';
    }

    public static function getModelLabel(): string
    {
        return 'Icon';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Icons';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Upload SVG Icon')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Icon Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., Settings, Home, Save')
                    ->hint('Give your icon a name'),

                Forms\Components\FileUpload::make('file')
                    ->label('SVG File')
                    ->required()
                    ->acceptedFileTypes(['image/svg+xml'])
                    ->maxSize(500)
                    ->storeFiles(false)
                    ->hint('Upload a clean SVG (max 500KB)'),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('format')
                    ->label('Format')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('color')
                    ->label('Color')
                    ->sortable(),

                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Uploaded By')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('format')
                    ->options(['svg' => 'SVG', 'png' => 'PNG']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkDeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIcons::route('/'),
            'create' => CreateIcon::route('/create'),
            'edit' => EditIcon::route('/{record}/edit'),
        ];
    }
}
