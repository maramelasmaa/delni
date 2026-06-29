<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppReviewDemoAccountResource\Pages;
use App\Models\User;
use App\Services\UserSuspensionService;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppReviewDemoAccountResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string
    {
        return 'App Review';
    }

    public static function getNavigationLabel(): string
    {
        return 'Demo Accounts';
    }

    public static function getModelLabel(): string
    {
        return 'Demo Account';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Demo Accounts';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'app_review_moderator']) === true;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return false;
    }

    public static function canDelete(mixed $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_suspended')
                    ->label('Suspended')
                    ->boolean(),
                Tables\Columns\TextColumn::make('suspension_reason')
                    ->label('Suspension reason')
                    ->placeholder('None')
                    ->limit(60),
            ])
            ->recordActions([
                Action::make('suspend_demo_account')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->is_suspended && $record->id !== auth()->id())
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->default('Demo abusive account suspended during App Review moderation test.')
                            ->required(),
                    ])
                    ->action(fn (User $record, array $data, UserSuspensionService $service) => $service->suspend($record, $data['reason'])),
                Action::make('reinstate_demo_account')
                    ->label('Reinstate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->is_suspended)
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->default('Demo account restored for App Review testing.')
                            ->required(),
                    ])
                    ->action(fn (User $record, array $data, UserSuspensionService $service) => $service->reinstate($record, $data['reason'])),
            ])
            ->paginated([10, 25]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('email', [
                'reviewer-user@delni.ly',
                'reviewer-provider@delni.ly',
                'reviewer-seeded-author@delni.ly',
            ])
            ->with('roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppReviewDemoAccounts::route('/'),
        ];
    }
}
