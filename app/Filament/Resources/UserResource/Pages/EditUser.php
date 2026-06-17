<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use App\Services\SuperAdminGuardService;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecordWithBack
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            Actions\DeleteAction::make()
                ->visible(fn () => SuperAdminGuardService::canDeleteUser($this->record))
                ->tooltip(fn () => ! SuperAdminGuardService::canDeleteUser($this->record) ? __('filament.help_text.super_admin_delete_blocked') : null),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return UserResource::fillUserFormData($data, $this->record);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Model {
            $accountData = UserResource::accountData($data);

            if (array_key_exists('password', $accountData) && filled($accountData['password'])) {
                $accountData['password'] = Hash::make($accountData['password']);
            }

            $record->update($accountData);
            UserResource::saveProviderTabs($record, $data);

            return $record;
        });
    }
}
