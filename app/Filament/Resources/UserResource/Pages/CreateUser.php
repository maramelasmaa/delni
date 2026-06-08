<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\ProviderCreationService;
use App\Services\SuperAdminGuardService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): User {
            $role = SuperAdminGuardService::preventSuperAdminAssignment(new User, $data['role'] ?? 'user');
            $accountData = UserResource::accountData($data);

            if (array_key_exists('password', $accountData) && filled($accountData['password'])) {
                $accountData['password'] = Hash::make($accountData['password']);
            }

            $record = User::query()->create($accountData);
            $record->assignRole($role);

            // CRITICAL: Create provider profile synchronously, inside transaction.
            // This ensures profile creation never depends on queue workers.
            if ($role === 'provider') {
                $service = app(ProviderCreationService::class);
                $service->createProfileForUser($record);
            }

            UserResource::saveProviderTabs($record, $data);

            return $record;
        });
    }
}
