<?php

namespace App\Filament\Provider\Resources\PortfolioResource\Pages;

use App\Filament\Provider\Resources\PortfolioResource;
use App\Filament\Support\Pages\CreateRecordWithBack;
use Filament\Notifications\Notification;

class CreatePortfolioItem extends CreateRecordWithBack
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

            redirect(PortfolioResource::getUrl('index'))->send();
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $profile = auth()->user()->profile;
        $data['profile_id'] = $profile->id;

        // Validate portfolio item limit (max 2)
        if ($profile->portfolioItems()->count() >= 2) {
            $this->halt();
        }

        // Validate image limit (max 4 per item)
        if (isset($data['images']) && is_array($data['images']) && count($data['images']) > 4) {
            $this->halt();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
