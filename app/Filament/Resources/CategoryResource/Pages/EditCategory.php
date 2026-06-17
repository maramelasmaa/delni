<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Support\Pages\EditRecordWithBack;
use App\Services\SvgIconService;
use Filament\Actions;
use Filament\Notifications\Notification;

class EditCategory extends EditRecordWithBack
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackHeaderAction(),
            Actions\DeleteAction::make(),
        ];
    }
}
