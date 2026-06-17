<?php

namespace App\Filament\Provider\Resources\CredentialsResource\Pages;

use App\Filament\Provider\Resources\CredentialsResource;
use App\Filament\Provider\Resources\ProfileResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCredentials extends ListRecords
{
    protected static string $resource = CredentialsResource::class;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user?->profile) {
            Notification::make()
                ->title('لم ينشأ ملفك التجاري بعد')
                ->body('يجب إنشاء ملفك التجاري أولاً قبل إضافة الشهادات والخبرات. اذهب إلى "ملفي التجاري" وأنشئ ملفك أولاً.')
                ->danger()
                ->send();

            redirect(ProfileResource::getUrl('create', panel: 'provider'))->send();
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة شهادة أو خبرة')
                ->tooltip('أضف شهاداتك وخبراتك'),
        ];
    }
}
