<?php

namespace App\Filament\Resources\SubcategoryResource\Pages;

use App\Filament\Resources\SubcategoryResource;
use App\Services\SvgIconService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSubcategory extends EditRecord
{
    protected static string $resource = SubcategoryResource::class;

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
                    ->title('Icon upload failed')
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
            Actions\DeleteAction::make(),
        ];
    }
}
