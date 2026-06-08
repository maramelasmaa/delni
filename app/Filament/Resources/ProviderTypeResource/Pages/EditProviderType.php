<?php

namespace App\Filament\Resources\ProviderTypeResource\Pages;

use App\Filament\Resources\ProviderTypeResource;
use App\Models\Profile;
use App\Models\ProviderType;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProviderType extends EditRecord
{
    protected static string $resource = ProviderTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, ProviderType $record): void {
                    if (Profile::query()->where('provider_type', $record->code)->exists()) {
                        Notification::make()
                            ->title('لا يمكن حذف نوع مستخدم في ملفات مقدمي الخدمة.')
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
