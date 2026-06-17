<?php

namespace App\Filament\Resources\ProviderTypeResource\Pages;

use App\Filament\Resources\ProviderTypeResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use App\Models\Profile;
use App\Models\ProviderType;
use Filament\Actions;
use Filament\Notifications\Notification;

class EditProviderType extends EditRecordWithBack
{
    protected static string $resource = ProviderTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
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
