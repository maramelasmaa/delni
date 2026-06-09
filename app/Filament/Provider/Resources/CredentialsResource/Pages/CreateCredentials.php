<?php

namespace App\Filament\Provider\Resources\CredentialsResource\Pages;

use App\Filament\Provider\Resources\CredentialsResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCredentials extends CreateRecord
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

            redirect(route('filament.provider.auth.profile'))->send();
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['profile_id'] = auth()->user()->profile->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
