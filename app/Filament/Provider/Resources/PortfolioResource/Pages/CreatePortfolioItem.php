<?php

namespace App\Filament\Provider\Resources\PortfolioResource\Pages;

use App\Filament\Provider\Resources\PortfolioResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePortfolioItem extends CreateRecord
{
    protected static string $resource = PortfolioResource::class;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user?->profile) {
            Notification::make()
                ->title('لم ينشأ ملفك التجاري بعد')
                ->body('يجب إنشاء ملفك التجاري أولاً قبل إضافة المشاريع. اذهب إلى "ملفي التجاري" وأنشئ ملفك أولاً.')
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
