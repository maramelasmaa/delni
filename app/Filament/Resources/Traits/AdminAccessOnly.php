<?php

namespace App\Filament\Resources\Traits;

trait AdminAccessOnly
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function canEdit(mixed $record): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function canDelete(mixed $record): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function canView(mixed $record): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') === true;
    }
}
