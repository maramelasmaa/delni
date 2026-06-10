<?php

namespace App\Filament\Resources\IconResource\Pages;

use App\Filament\Resources\IconResource;
use App\Services\SvgIconService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateIcon extends CreateRecord
{
    protected static string $resource = IconResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            $service = app(SvgIconService::class);
            $icon = $service->uploadAndColorize(
                $data['file'],
                $data['name']
            );

            Notification::make()
                ->success()
                ->title('✅ Icon uploaded!')
                ->body('Icon saved and colored orange. Size: 24x24px')
                ->send();

            return [];
        } catch (\Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Upload failed')
                ->body($e->getMessage())
                ->send();

            throw $e;
        }
    }
}
