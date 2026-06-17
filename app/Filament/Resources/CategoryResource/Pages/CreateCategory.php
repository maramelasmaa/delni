<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Support\Pages\CreateRecordWithBack;
use App\Services\SvgIconService;
use Filament\Notifications\Notification;

class CreateCategory extends CreateRecordWithBack
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['svg_file'])) {
            try {
                $file = $data['svg_file'];
                $service = app(SvgIconService::class);
                $icon = $service->uploadAndColorize(
                    $file,
                    $data['name'].' Icon'
                );
                $data['icon_id'] = $icon->id;
            } catch (\Throwable $e) {
                Notification::make()
                    ->danger()
                    ->title(__('filament.notifications.icon_upload_failed'))
                    ->body($e->getMessage())
                    ->send();
            }
        }
        unset($data['svg_file']);

        return $data;
    }
}
